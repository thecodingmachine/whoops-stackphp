<?php
namespace Whoops\StackPhp;
				
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
				
/**
 * This class is wrapping the Whoops PHP library into a a StackPHP compatible middleware.
 */
class WhoopsMiddleWare implements HttpKernelInterface {
	
	/**
	 * @var HttpKernelInterface
	 */
	private $router;
	
	private $catchExceptions;
	private $catchErrors;
	private $whoops;
	
	/**
	 * @Important
	 * @param HttpKernelInterface $router The default router (the router we will catch exceptions from).
	 * @param boolean|ConditionInterface|callable $catchExceptions Whether we should catch exception or not
	 * @param boolean|ConditionInterface|callable $catchErrors Whether we should catch errors or not
	 */
	public function __construct(HttpKernelInterface $router, $catchExceptions = true, $catchErrors = true) {
		$this->router = $router;
		$this->catchExceptions = $catchExceptions;
		$this->catchErrors = $catchErrors;
	}
	
	/**
	 * Handles a Request to convert it to a Response.
	 *
	 * When $catch is true, the implementation must catch all exceptions
	 * and do its best to convert them to a Response instance.
	 *
	 * @param Request $request A Request instance
	 * @param int     $type    The type of the request
	 *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 * @param bool    $catch Whether to catch exceptions or not
	 *
	 * @return Response A Response instance
	 *
	 * @throws \Exception When an Exception occurs during processing (and $catch is set to false)
	 */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true) {
		if ($this->resolveBool($this->catchErrors)) {
			$whoops = $this->getWhoops();
			$whoops->register();
		}
		
		if ($catch && $this->resolveBool($this->catchExceptions)) {
			try {
				return $this->router->handle($request, $type, false);
			} catch (\Exception $e) {
				$method = Run::EXCEPTION_HANDLER;
				
				ob_start();
				$whoops = $this->getWhoops();
				$whoops->$method($e);
				$response = ob_get_clean();
				$code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
				return new Response($response, $code);
			}
		}else{
			return $this->router->handle($request, $type);
		}
	}
	
	protected function resolveBool($value) {
		if ($value instanceof ConditionInterface) {
			return $value->isOk($this);
		} elseif (is_callable($value)) {
			return $value();
		} else {
			return $value;
		}
	}
	
	/**
	 * Returns a Whoops\Run instance (creates the instance if it does not exist).
	 * @return \Whoops\Run
	 */
	protected function getWhoops() {
		if ($this->whoops === null) {
			$this->whoops = new Run();
			$this->whoops->pushHandler(new PrettyPageHandler());
		}
		return $this->whoops;
	}
	
}