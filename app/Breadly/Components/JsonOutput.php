<?php namespace App\Breadly\Components;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class JsonOutput
{

    private $response = null;

    public function __construct()
    {
        $this->response          = new \stdClass();
        $this->response->code    = 200;
        $this->response->message = '';
        $this->response->errors  = [];
        $this->response->result  = null;
    }

    public static function make()
    {
        return new static;
    }

    public function errors($errors, $code = 500)
    {
        $errors = is_array($errors) ? $errors : [$errors];

        $this->response->errors  = $errors;
        $this->response->message = $errors[0];

        if ($code) {
            $this->response->code = $code;
        }

        return $this;
    }

    public function error($error, $code = 500)
    {
        $this->response->errors[] = $error;
        if (empty($this->response->message)) {
            $this->response->message = $error;
        }

        if ($code) {
            $this->code($code);
        }

        return $this;
    }


    public function message($message, $code = 200)
    {
        $this->response->message = $message ?: '';
        if ($code) {
            $this->code($code);
        }

        if (empty($this->response->result)) {
            $this->response->result = $message;
        }

        return $this;
    }

    public function code($code = 200)
    {
        $this->response->code = (int)$code;

        return $this;
    }

    public function object($object)
    {
        $this->response->result = $object;
        if (is_string($object)) {
            $this->response->message = $object;
        }

        return $this;
    }

    public function collection($collection)
    {
        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $this->response->result = $collection;

        return $this;
    }

    public function addExtra($key, $value)
    {
        $this->response->{$key} = $value;

        return $this;
    }

    public function getJson()
    {
        return $this->response;
    }

    public function __toString()
    {
        return json_encode($this->response);
    }


    public static function httpResponse($content, $status = 200, $extras = [])
    {
        $jsonOut = null;

        if ($status >= 400) {
            $jsonOut = JsonOutput::make()->errors($content)->code($status);
        } elseif ($content instanceof Collection || (is_array($content) && !Arr::isAssoc($content))) {
            $jsonOut = JsonOutput::make()->collection($content)->code($status);
        } elseif (is_object($content) || (is_array($content) && Arr::isAssoc($content))) {
            $jsonOut = JsonOutput::make()->object($content)->code($status);
        } else {
            $jsonOut = JsonOutput::make()->object($content)->code($status);
        }

        foreach ($extras as $key => $value) {
            $jsonOut->addExtra($key, $value);
        }

        $routeName = app('request')->route()->getName();
        $routePath = app('request')->path();

        $tag = app('request')->header('X-Request-Tag');
        if (!empty($tag)) {
            $jsonOut->addExtra('tag', $tag);
        }

        $response = response($jsonOut, $status)
            ->header('Content-Type', 'application/json')
            ->header('X-Request-Route', $routeName)
            ->header('X-Request-Uri', $routePath);


        if (!empty($tag)) {
            $response->header('X-Request-Tag', $tag);
        }

        return $response;
    }

}