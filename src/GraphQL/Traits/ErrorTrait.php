<?php

namespace Audentio\LaravelGraphQL\GraphQL\Traits;

use Audentio\LaravelGraphQL\GraphQL\Errors\InvalidParameterError;
use Audentio\LaravelGraphQL\GraphQL\Errors\NotFoundError;
use Audentio\LaravelGraphQL\GraphQL\Errors\PermissionError;
use Audentio\LaravelGraphQL\GraphQL\Errors\ValidationError;
use Audentio\LaravelGraphQL\LaravelGraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\MessageBag;

trait ErrorTrait
{
    public static $ERR_PERMISSION = 'permission';
    public static $ERR_NOT_FOUND = 'notFound';
    public static $ERR_ = 'notFound';

    public function typedError($info, $error, $rootItem, $errorType = null, $defaultErrorType = LaravelGraphQL::ERR_PERMISSION)
    {
        switch ($errorType) {
            case LaravelGraphQL::ERR_INVALID_PARAMETER:
                $this->invalidParameterError($info, $error);
                break;

            case LaravelGraphQL::ERR_NOT_FOUND:
                $this->notFoundError($info, $error);
                break;

            case LaravelGraphQL::ERR_VALIDAATION:
                $this->validationError($info, $error, $rootItem);
                break;

            case LaravelGraphQL::ERR_PERMISSION:
            default:
                $this->permissionError($info, $error);
        }
    }

    /**
     * @param string $message
     * @return bool
     * @throws NotFoundError
     */
    public function notFoundError($info = null, $message = null)
    {
        $this->standardizeArguments($info, $message);

        throw new NotFoundError($message, $info);
    }

    public function cannotQueryDirectly($info = null, $message = null)
    {
        $this->standardizeArguments($info, $message);
        if (!$message) {
            $message = 'You cannot access this query directly';
        }

        $this->permissionError($info, $message);
    }

    /**
     * @param string $message
     * @return bool
     * @throws PermissionError
     */
    public function permissionError($info = null, $message = null)
    {
        $this->standardizeArguments($info, $message);

        throw new PermissionError($message, $info);
    }

    public function validationError($info = null, $validatorMessages = null, $rootItem = null)
    {
        $message = __('validation.genericMessage');
        $this->standardizeArguments($info, $message);

        $validationError = new ValidationError($message, $info);

        if ($rootItem) {
            if ($validatorMessages instanceof MessageBag) {
                $messages = $validatorMessages->getMessages();
            } else {
                $messages = $validatorMessages;
            }

            $messages = [
                $rootItem => $messages,
            ];
            $validatorMessages = new MessageBag($messages);
        }
        $validationError->setValidatorMessages($validatorMessages);

        throw $validationError;
    }

    /**
     * @param string $message
     * @return bool
     * @throws InvalidParameterError
     */
    public function invalidParameterError($info = null, $message = null)
    {
        $this->standardizeArguments($info, $message);

        throw new InvalidParameterError($message, $info);
    }

    protected function standardizeArguments(&$info = null, &$message = null)
    {
        if ($info !== null && !$info instanceof ResolveInfo) {
            $message = $info;
            $info = null;
        }

        if ($message === null) {
            $message = '';
        }
    }
}
