<?php

namespace {
    /**
     * Errors code used
     * @class ErrorCodeAuth
     * @see ErrorCodeAuth
     * @brief List all error code errors
     * @see ErrorCodeAuth
     */
    class ErrorCodeAuth
    {
        /**
         * @errorCode in case of POST
         */
        const AUTH0001 = 'Error, access forbidden';
    }
}
namespace Anakeen\Routes\Authent {
    class Exception extends \Dcp\Exception
    {
        protected $httpStatus = 400;
        protected $httpMessage = "Dcp Exception";
        /**
         *
         * @param int $httpStatus
         * @param string $httpMessage
         */
        public function setHttpStatus($httpStatus, $httpMessage = "")
        {
            $this->httpStatus = $httpStatus;
            $this->httpMessage = $httpMessage;
        }
    }
}