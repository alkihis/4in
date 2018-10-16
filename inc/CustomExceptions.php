<?php

// CRÉATION D'EXCEPTIONS PARTICULIERES POUR GERER LE 403, 404
// L'erreur 500 sera automatiquement générée lors de la rencontre
// avec toute autre exception que ces deux là

/* 403 */
class ForbiddenPageException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

/* 404 */
class PageNotFoundException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

/* Fonction non implémentée */
class NotImplementedException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}