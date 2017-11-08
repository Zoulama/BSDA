<?php

namespace Provisioning\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Provisioning\Exceptions\InvalidPhoneNumberException;
use Provisioning\Exceptions\PSTNNumberExistsException;
use Provisioning\Exceptions\PSTNNumberNotFoundException;
use Provisioning\Exceptions\InvalidPSTNRangeException;
use Provisioning\Exceptions\ExtensionAlreadyAssignedException;
use Provisioning\Exceptions\UserExtensionNotFoundException;
use Provisioning\Exceptions\PrestationNotFoundException;
use Provisioning\Exceptions\ExtensionsGroupNotFoundException;
use Provisioning\Exceptions\SpeedDialNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException)
            return response(null, $e->getStatusCode());
        elseif ($e instanceof PSTNNumberNotFoundException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 404);
        elseif ($e instanceof UserExtensionNotFoundException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 404);
        elseif ($e instanceof InvalidPhoneNumberException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 400);
        elseif ($e instanceof PSTNNumberExistsException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 400);
        elseif ($e instanceof InvalidPSTNRangeException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 400);
        elseif ($e instanceof ExtensionAlreadyAssignedException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 400);
        elseif ($e instanceof PrestationNotFoundException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 404);
        elseif ($e instanceof ExtensionsGroupNotFoundException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 404);
        elseif ($e instanceof SpeedDialNotFoundException)
            return response()->json(['error' => ['message' => $e->getMessage()]], 404);
        elseif ($e instanceof NotFoundHttpException)
            return response(null, 404);
        elseif ($e instanceof ModelNotFoundException) {
            if ($e->getModel() == 'Provisioning\ComptaPrestation')
                return response()->json(['error' => ['message' => 'Prestation not found']], 404);
        }

        return parent::render($request, $e);
    }
}
