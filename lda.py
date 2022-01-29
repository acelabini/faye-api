from glob import glob
import re
import string
import funcy as fp
from gensim import models
from gensim.corpora import Dictionary, MmCorpus
import nltk
nltk.data.path.append('/usr/share/nltk_data/')
import pandas as pd
import pyLDAvis.gensim as gensimvis
import pyLDAvis
import sys

# quick and dirty....
EMAIL_REGEX = re.compile(r"[a-z0-9\.\+_-]+@[a-z0-9\._-]+\.[a-z]*")
FILTER_REGEX = re.compile(r"[^a-z '#]")
TOKEN_MAPPINGS = [(EMAIL_REGEX, "#email"), (FILTER_REGEX, ' ')]

modelName = sys.argv[2];
rPath = sys.argv[1]+'/'+modelName;
numOfTopics = sys.argv[3];

def tokenize_line(line):
    res = line.lower()
    for regexp, replacement in TOKEN_MAPPINGS:
        res = regexp.sub(replacement, res)
    return res.split()

def tokenize(lines, token_size_filter=2):
    tokens = fp.mapcat(tokenize_line, lines)
    return [t for t in tokens if len(t) > token_size_filter]


def load_doc(filename):
    group, doc_id = filename.split('/')[-2:]
    with open(filename, 'r') as f:
        doc = f.readlines()
    return {'group': group,
            'doc': doc,
            'tokens': tokenize(doc),
            'id': doc_id}

docs = pd.DataFrame(list(map(load_doc, glob(rPath+'/data/*')))).set_index(['group','id'])
docs.head()

def nltk_stopwords():
    return set(nltk.corpus.stopwords.words('english'))

def prep_corpus(docs, additional_stopwords=set(), no_below=5, no_above=0.5):
  print('Building dictionary...')
  dictionary = Dictionary(docs)
  stopwords = nltk_stopwords().union(additional_stopwords)
  stopword_ids = map(dictionary.token2id.get, stopwords)
  dictionary.filter_tokens(stopword_ids)
  dictionary.compactify()
  dictionary.filter_extremes(no_below=no_below, no_above=no_above, keep_n=None)
  dictionary.compactify()
  print('Building corpus...')
  corpus = [dictionary.doc2bow(doc) for doc in docs]
  return dictionary, corpus

dictionary, corpus = prep_corpus(docs['tokens'])
MmCorpus.serialize(rPath+'/model.mm', corpus)
dictionary.save(rPath+'/model.dict')
lda = models.ldamodel.LdaModel(corpus=corpus, id2word=dictionary, num_topics=numOfTopics, passes=1)
lda.save(rPath+'/model.model')
vis_data = gensimvis.prepare(lda, corpus, dictionary)
pyLDAvis.save_html(vis_data, rPath+'/lda.html')
