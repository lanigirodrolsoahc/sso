<?php

namespace SSO;

class Membership
{
	use Dates;
	use VirtualObject;

	protected 	$defaultOrderBy = Belonging::ID,
				$tableName      = 'membership';

	public
	const   TYPE_USER   = 'user',
			TYPE_GROUP  = 'group',
			TYPE_RIGHT 	= 'right';

	public  $f_id               = 'id',
			$f_subjectType      = 'subjectType',
			$f_subject          = 'subject',
			$f_targetType       = 'targetType',
			$f_target           = 'target',
			$f_start 			= 'start',
			$f_stop 			= 'stop';

	/**
     * gets all direct groups for current user of `User::Instance()`
	 *
	 * @param	 ?bool		$considerDateLimits
	 *
	 * @return 	array<\stdClass>
     */
	public
	function getGroupsForUser ( bool $considerDateLimits = false ) : array
	{
		$list = ( $user = User::Instance() )->isRead()
			? $this
				->fill(
					\Std::__new()
						->{ $this->f_subject }( (int) $user->getVirtual()->{ $user->f_id } )
						->{ $this->f_subjectType }( self::TYPE_USER )
						->{ $this->f_targetType }( self::TYPE_GROUP )
				)
				->readAll()
			: [];

		if ( $considerDateLimits )
		{
			$now = $this->now()->format( $this->fmt_timestamp );

			$list = array_filter($list, fn ( \stdClass $item ) =>
				( is_null( $start = $item->{ $this->f_start } ) || $start < $now )
				&& ( is_null( $stop = $item->{ $this->f_stop } ) || $stop > $now )
			);
		}

		return $list;
	}

	/**
	 * gets all direct parent groups for given group identifier
	 *
	 * @param 	int 	$groupId
	 * @param 	?bool	$getChildren
	 *
	 * @return 	array<\stdClass>
	 */
	public
	function getGroupsForGroup ( int $groupId, bool $getChildren = false ) : array
	{
		return $this
			->fill(
				\Std::__new()
					->{ $getChildren ? $this->f_target : $this->f_subject }($groupId)
					->{ $this->f_subjectType }( self::TYPE_GROUP )
					->{ $this->f_targetType }( self::TYPE_GROUP )
			)
			->readAll();
	}

	/**
	 * takes a recursive look at groups and their children
	 *
	 * @param 	array<int>		$pool 		of candidates
	 * @param 	?array<int> 	$results
	 *
	 * @return 	array<\stdClass>
	 */
	public
	function getGroupsProgeny ( array $pool, array $results = [] ) : array
	{
		$copy = $results;

		foreach ( $pool as $groupId )
			if ( ! in_array($groupId, $results, true) )
			{
				$results[] = $groupId;

				foreach ( $this->getGroupsForGroup( (int) $groupId, true ) as $child )
					if ( ! in_array($childId = (int) $child->{ $this->f_subject }, $results, true) )
						$results[] = $childId;
			}

		return empty( $news = array_diff($results, $copy) )
			? ( $group = Group::Instance() )
				->fill(
					\Std::__new()
						->{ $group->f_id }( empty($results) ? [0] : $results ),
					$group->type_in
				)
				->readAll()
			: $this->{ __FUNCTION__ }($news, $results);
	}

	/**
	 * gets all users who are direct children of given group
	 *
	 * @param 	int 	$groupId
	 *
	 * @return 	array<\stdClass>
	 */
	public
	function getUsersForGroup ( int $groupId ) : array
	{
		return $this
			->fill(
				\Std::__new()
					->{ $this->f_target }($groupId)
					->{ $this->f_subjectType }( self::TYPE_USER )
					->{ $this->f_targetType }( self::TYPE_GROUP )
			)
			->readAll();
	}

	/**
	 * determines if a limiting date is to be found, according to given type
	 *
	 * @param 	string 	$type
	 *
	 * @return 	\DateTime|false
	 */
	private
	function isDateLimited ( string $type )
	{
		return $this->isRead() && ! is_null( $questioned = $this->getVirtual()->$type ) ? new \DateTime( $questioned, $this->parisTimeZone() ) : false;
	}

	/**
	 * determines if current membership is limited by a start date
	 *
	 * @return 	\DateTime|false
	 */
	public
	function starts ()
	{
		return $this->isDateLimited( $this->f_start );
	}

	/**
	 * determines if current membership is limited by an ending date
	 *
	 * @return 	\DateTime|false
	 */
	public
	function stops ()
	{
		return $this->isDateLimited( $this->f_stop );
	}
}
