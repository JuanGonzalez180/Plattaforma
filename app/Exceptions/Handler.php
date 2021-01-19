<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
   
    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {  
            if ($exception instanceof ValidationException) {
                return $this->convertValidationExceptionToResponseNew($exception, $request);
            }

            if ($exception instanceof ModelNotFoundException) {
                $model = Str::lower(class_basename($exception->getModel()));
                return $this->errorResponse("No existe ninguna instancia de {$model} con el id especificado", 404);
            }

            if ($exception instanceof AuthenticationException) {
                return $this->unauthenticated($request, $exception);
            }

            if ($exception instanceof AuthorizationException) {
                return $this->errorResponse( 'No posee permisos para ejecutar esta acción', 403 );
            }

            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse( 'No se encontró la url especificada', 404 );
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse( 'El método especificado en la petición no es válido', 405 );
            }

            if ($exception instanceof HttpException) {
                return $this->errorResponse( $exception->getMessage(), $exception->getStatusCode() );
            }

            if ($exception instanceof QueryException) {
                $codigo = $exception->errorInfo[1];
                if( $codigo == 1451 ){
                    return $this->errorResponse('No se puede eliminar de forma permanente el recurso porque está relacionado con algún otro.', 409);
                }
            }

            if( config('app.debug') ){
                return parent::render($request, $exception);
            }

            return $this->errorResponse('Falla inesperada, intente luego.', 500);
        }else{
            return parent::render($request, $exception);
        }
    }

    protected function unauthenticated( $request, AuthenticationException $exception ){
        return $this->errorResponse('No autenticado', 401);
    }

     /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function convertValidationExceptionToResponseNew(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        return $this->errorResponse($errors, 422);
    }
}