<?php

class Controller {
    protected $data; // Mixed data registred by the page
    protected $page_title = null; // Page title;  If null: no title
    protected $view = null; // Callback function to call on invoke()


    public function __construct(array $data, ?string $title) {
        $this->data = $data;
        if ($title) {
            $this->page_title = $title;
        }
    }
    public function __invoke() {
        if ($this->view === null) {
            throw new UnexpectedValueException("No view function defined");
        }
        if (! is_callable($this->view)) {
            throw new UnexpectedValueException("View function is not callable");
        }

        ($this->view)($this);
    }

    public function setTitle(?string $title) : ?string {
        $this->page_title = $title;
        return $this->page_title;
    }

    public function getTitle() : ?string {
        return $this->page_title;
    }

    public function setViewFunction(string $func_name) : string {
        $this->view = $func_name;
        return $this->view;
    }

    public function getData() : array {
        return $this->data;
    }

    public function setData(array $data) : array {
        $this->data = $data;
        return $this->data;
    }
}
