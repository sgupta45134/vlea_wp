<?php

namespace Codemanas\ZoomPro\Core;

use \ReflectionClass as ReflectionClass;
use \Exception as Exception;

/**
 * Class Container
 *
 * @since 1.0.0
 * @author Deepen
 * @copyright 2020. All rights reserved. CodeManas
 */
class Container {
	/**
	 * @var array
	 */
	protected $instances = [];

	/**
	 * @param      $abstract
	 * @param null $concrete
	 */
	public function set( $abstract, $concrete = null ) {
		if ( $concrete === null ) {
			$concrete = $abstract;
		}
		$this->instances[ $abstract ] = $concrete;
	}

	/**
	 * @param       $abstract
	 * @param array $parameters
	 *
	 * @return mixed|null|object
	 * @throws Exception
	 */
	public function get( $abstract, $parameters = [] ) {
		// if we don't have it, just register it
		if ( ! isset( $this->instances[ $abstract ] ) ) {
			$this->set( $abstract );
		}

		return $this->resolve( $this->instances[ $abstract ], $parameters );
	}

	/**
	 * Resolve single
	 *
	 * @param $concrete
	 * @param $parameters
	 *
	 * @return object
	 * @throws \ReflectionException
	 */
	public function resolve( $concrete, $parameters ) {
		if ( $concrete instanceof \Closure ) {
			return $concrete( $this, $parameters );
		}

		$reflector = new ReflectionClass( $concrete );
		// check if class is instantiable
		if ( ! $reflector->isInstantiable() ) {
			throw new Exception( "Class {$concrete} is not instantiable" );
		}

		// get class constructor
		$constructor = $reflector->getConstructor();
		if ( is_null( $constructor ) ) {
			// get new instance from class
			return $reflector->newInstance();
		}

		// get constructor params
		$parameters   = $constructor->getParameters();
		$dependencies = $this->getDependencies( $parameters );

		// get new instance with dependencies resolved
		return $reflector->newInstanceArgs( $dependencies );
	}

	/**
	 * get all dependencies resolved
	 *
	 * @param $parameters
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDependencies( $parameters ) {
		$dependencies = [];
		if ( ! empty( $parameters ) ) {
			foreach ( $parameters as $parameter ) {
				// get the type hinted class
				$dependency = $parameter->getType() && ! $parameter->getType()->isBuiltin() ? new ReflectionClass( $parameter->getType()->getName() ) : null;
				if ( $dependency === null ) {
					// check if default value for a parameter is available
					if ( $parameter->isDefaultValueAvailable() ) {
						// get default value of parameter
						$dependencies[] = $parameter->getDefaultValue();
					} else {
						throw new Exception( "Can not resolve class dependency {$parameter->name}" );
					}
				} else {
					// get dependency resolved
					$dependencies[] = $this->get( $dependency->name );
				}
			}
		}

		return $dependencies;
	}
}