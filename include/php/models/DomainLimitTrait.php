<?php

trait DomainLimitTrait
{
	/**
	 * @param array|User|null $limitedBy
	 *
	 * @return bool
	 */
	public function isInLimitedDomains($limitedBy = null)
	{
		if(!defined('ADMIN_DOMAIN_LIMITS_ENABLED')) {
			return true;
		}
		if(is_null($limitedBy)){
			return static::isInLimitedDomains(Auth::getUser());
		}
		elseif($limitedBy instanceof User) {
			/** @var User $limitedBy */
			return $limitedBy->isDomainLimited() && static::isInLimitedDomains($limitedBy->getDomainLimits());
		}

		if(!is_array($limitedBy)){
			throw new InvalidArgumentException;
		}

		/** @var string|array|string[] $domain */
		$domain = $this->getDomain();

		if(is_string($domain)) {
			return in_array($domain, $limitedBy);
		}

		foreach($domain as $d){
			if(!in_array($d, $limitedBy)) {
				return false;
			}
		}

		return true;
	}


	/**
	 * @param ModelCollection|static[] $collection
	 * @param array|User|null $limitedBy
	 *
	 * @return ModelCollection|static[]
	 */
	protected static function filterModelCollectionByLimitedDomains($collection, $limitedBy = null)
	{
		return $collection->searchAll(function($model) use ($limitedBy){
			/** @var static $model */
			return $model->isInLimitedDomains($limitedBy);
		});
	}


	/**
	 * @param array|User|null $limitedBy
	 *
	 * @return ModelCollection|static[]
	 */
	public static function getByLimitedDomains($limitedBy = null)
	{
		return static::filterModelCollectionByLimitedDomains(static::findAll(), $limitedBy);
	}
}