<?php

namespace App\Http\Responses;

class ApiResponse
{

    protected $data;

    protected $cookie;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    public function getCookie()
    {
        return $this->cookie;
    }
}
