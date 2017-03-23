<?php
namespace TYPO3\CMS\Cal\Service;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * This class handles all cal(endar)-rights of a current logged-in user
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class RightsService extends \TYPO3\CMS\Cal\Service\BaseService {
	var $confArr = array ();
	
	public function __construct() {
		parent::__construct ();
		$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
	}
	function isLoggedIn() {
		return $GLOBALS ['TSFE']->loginUser;
	}
	function getUserGroups() {
		if ($this->isLoggedIn ()) {
			return $GLOBALS ['TSFE']->fe_user->groupData ['uid'];
		}
		return array ();
	}
	function getUserId() {
		if ($this->isLoggedIn () && ! empty ($GLOBALS ['TSFE']->fe_user->user ['uid'])) {
			$val = intval ($GLOBALS ['TSFE']->fe_user->user ['uid']);
			return $val;
		}
		return - 1;
	}
	function getUserName() {
		if ($this->isLoggedIn ()) {
			$val = $GLOBALS ['TSFE']->fe_user->user ['username'];
			return $val;
		}
		return - 1;
	}
	function isCalEditable() {
		if ($this->conf ['rights.'] ['edit'] == 1)
			return true;
		return false;
	}
	function isCalAdmin() {
		if ($this->isLoggedIn ()) {
			$users = explode (',', $this->conf ['rights.'] ['admin.'] ['user']);
			$groups = explode (',', $this->conf ['rights.'] ['admin.'] ['group']);
			if (array_search ($this->getUserId (), $users) !== false)
				return true;
			$userGroups = $this->getUserGroups ();
			foreach ($groups as $key => $group) {
				if (array_search (ltrim ($group), $userGroups) !== false)
					return true;
			}
		}
		return false;
	}
	function isAllowedToCreateEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.']);
	}
	function isAllowedToCreateEventInPast() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['inPast.']);
	}
	function isAllowedToCreateEventForTodayAndFuture() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['forTodayAndFuture.']);
	}
	function isAllowedToEditEventInPast() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['inPast.']);
	}
	function isAllowedToDeleteEventInPast() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['event.'] ['inPast.']);
	}
	function isAllowedToCreateEventHidden() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['hidden.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventCategory() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['category.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['category.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventCalendar() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['calendar_id.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['calendar_id.']))
			return true;
		return false;
	}
	function isAllowedToCreatePublicEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['publicEvents.']);
	}
	function isAllowedToCreateEventDateTime() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['startdate.'] ['public'] || $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['enddate.'] ['public'] || $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['starttime.'] ['public'] || $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['endtime.'] ['public'])) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['startdate.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['enddate.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['starttime.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['endtime.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventTitle() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['title.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventOrganizer() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['organizer.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['organizer.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventLocation() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['location.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['location.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventDescription() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['description.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventTeaser() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['teaser.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['teaser.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventRecurring() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['recurring.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['recurring.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventNotify() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['notify.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['notify.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventException() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['exception.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['exception.']))
			return true;
		return false;
	}
	function isAllowedToCreateEventShared() {
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['public'] && $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['shared.'] ['public']) {
			return true;
		}
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['shared.']))
			return true;
		return false;
	}
	function isAllowedToEditEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.']);
	}
	function isPublicAllowedToEditEvents() {
		return $this->conf ['rights.'] ['edit.'] ['event.'] ['public'] == 1;
	}
	function isAllowedToEditStartedEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['startedEvents.']);
	}
	function isAllowedToEditOnlyOwnEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['onlyOwnEvents.']);
	}
	function isAllowedToEditEventHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToEditEventCalendar() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['calendar_id.']))
			return true;
		return false;
	}
	function isAllowedToEditEventCategory() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['category.']))
			return true;
		return false;
	}
	function isAllowedToEditEventDateTime() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['startdate.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['enddate.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['starttime.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['endtime.']))
			return true;
		return false;
	}
	function isAllowedToEditEventTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToEditEventOrganizer() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['organizer.']))
			return true;
		return false;
	}
	function isAllowedToEditEventLocation() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['location.']))
			return true;
		return false;
	}
	function isAllowedToEditEventDescription() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToEditEventTeaser() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['teaser.']))
			return true;
		return false;
	}
	function isAllowedToEditEventRecurring() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['recurring.']))
			return true;
		return false;
	}
	function isAllowedToEditEventNotify() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['notify.']))
			return true;
		return false;
	}
	function isAllowedToEditEventException() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['event.'] ['fields.'] ['exception.']))
			return true;
		return false;
	}
	function isAllowedToDeleteEvents() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['event.']);
	}
	function isAllowedToDeleteOnlyOwnEvents() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['event.'] ['onlyOwnEvents.']);
	}
	function isAllowedToDeleteStartedEvents() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['event.'] ['startedEvents.']);
	}
	function isAllowedToCreateExceptionEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['exceptionEvent.']);
	}
	function isAllowedToEditExceptionEvent() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['exceptionEvent.']);
	}
	function isAllowedToDeleteExceptionEvents() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['exceptionEvent.']);
	}
	function isAllowedToCreateLocations() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.']);
	}
	function isAllowedToCreateLocationHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationDescription() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationName() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['name.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationStreet() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['street.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationZip() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['zip.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationCity() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['city.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationCountryZone() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['countryZone.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationCountry() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['country.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationPhone() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['phone.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationEmail() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['email.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationImage() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['image.']))
			return true;
		return false;
	}
	function isAllowedToCreateLocationLink() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['location.'] ['fields.'] ['link.']))
			return true;
		return false;
	}
	function isAllowedToEditLocation() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.']);
	}
	function isAllowedToEditLocationHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationDescription() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationName() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['name.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationStreet() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['street.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationZip() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['zip.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationCity() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['city.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationCountryZone() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['countryZone.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationCountry() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['country.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationPhone() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['phone.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationEmail() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['email.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationLogo() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['logo.']))
			return true;
		return false;
	}
	function isAllowedToEditLocationHomepage() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['fields.'] ['homepage.']))
			return true;
		return false;
	}
	function isAllowedToDeleteLocation() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['location.']);
	}
	
	// TODO: Remove for version 1.4.0, but keep this function for backwards compatibility until than
	function isAllowedToDeleteLocations() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['location.']);
	}
	function isAllowedToEditOnlyOwnLocation() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['location.'] ['onlyOwnLocation.']);
	}
	function isAllowedToDeleteOnlyOwnLocation() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['location.'] ['onlyOwnLocation.']);
	}
	function isAllowedToCreateOrganizer() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.']);
	}
	function isAllowedToCreateOrganizerHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerDescription() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerName() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['name.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerStreet() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['street.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerZip() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['zip.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerCity() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['city.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerPhone() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['phone.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerEmail() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['email.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerImage() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['image.']))
			return true;
		return false;
	}
	function isAllowedToCreateOrganizerLink() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['organizer.'] ['fields.'] ['link.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizer() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.']);
	}
	function isAllowedToEditOrganizerHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerDescription() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['description.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerName() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['name.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerStreet() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['street.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerZip() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['zip.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerCity() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['city.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerPhone() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['phone.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerEmail() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['email.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerLogo() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['logo.']))
			return true;
		return false;
	}
	function isAllowedToEditOrganizerHomepage() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['fields.'] ['homepage.']))
			return true;
		return false;
	}
	function isAllowedToDeleteOrganizer() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['organizer.']);
	}
	function isAllowedToEditOnlyOwnOrganizer() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['organizer.'] ['onlyOwnOrganizer.']);
	}
	function isAllowedToDeleteOnlyOwnOrganizer() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['organizer.'] ['onlyOwnOrganizer.']);
	}
	function isAllowedToCreateCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.']);
	}
	function isAllowedToCreateCalendarHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToCreateCalendarTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToCreateCalendarOwner() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['owner.']))
			return true;
		return false;
	}
	function isAllowedToCreateCalendarActivateFreeAndBusy() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['activateFreeAndBusy.']))
			return true;
		return false;
	}
	function isAllowedToCreateCalendarFreeAndBusyUser() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['freeAndBusyUser.']))
			return true;
		return false;
	}
	function isAllowedToCreateCalendarType() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['calendar.'] ['fields.'] ['type.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.']);
	}
	function isAllowedToEditOnlyOwnCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['onlyOwnCalendar.']);
	}
	function isAllowedToEditPublicCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['publicCalendar.']);
	}
	function isAllowedToEditCalendarHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendarType() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['type.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendarTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendarOwner() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['owner.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendarActivateFreeAndBusy() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['activateFreeAndBusy.']))
			return true;
		return false;
	}
	function isAllowedToEditCalendarFreeAndBusyUser() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['calendar.'] ['fields.'] ['freeAndBusyUser.']))
			return true;
		return false;
	}
	function isAllowedToDeleteCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['calendar.']);
	}
	function isAllowedToDeleteOnlyOwnCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['calendar.'] ['onlyOwnCalendar.']);
	}
	function isAllowedToDeletePublicCalendar() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['calendar.'] ['publicCalendar.']);
	}
	function isAllowedToCreateCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.']);
	}
	function isAllowedToCreateCategoryHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToCreateCategoryTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToCreateCategoryHeaderStyle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['headerstyle.']))
			return true;
		return false;
	}
	function isAllowedToCreateCategoryBodyStyle() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['bodystyle.']))
			return true;
		return false;
	}
	function isAllowedToCreateCategoryCalendar() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['calendar.']))
			return true;
		return false;
	}
	function isAllowedToCreateCategoryParent() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['parent.']))
			return true;
		return false;
	}
	function isAllowedToCreateGeneralCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['generalCategory.']);
	}
	function isAllowedToCreatePublicCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['publicCategory.']);
	}
	function isAllowedToCreateCategorySharedUser() {
		if ($this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['create.'] ['category.'] ['fields.'] ['sharedUser.']))
			return true;
		return false;
	}
	function isAllowedToEditCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.']);
	}
	function isAllowedToEditOnlyOwnCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['onlyOwnCategory.']);
	}
	function isAllowedToEditGeneralCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['generalCategory.']);
	}
	function isAllowedToEditPublicCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['publicCategory.']);
	}
	function isAllowedToEditCategoryHidden() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['hidden.']))
			return true;
		return false;
	}
	function isAllowedToEditCategoryTitle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['title.']))
			return true;
		return false;
	}
	function isAllowedToEditCategoryHeaderstyle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['headerstyle.']))
			return true;
		return false;
	}
	function isAllowedToEditCategoryBodystyle() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['bodystyle.']))
			return true;
		return false;
	}
	function isAllowedToEditCategoryCalendar() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['calendar.']))
			return true;
		return false;
	}
	function isAllowedToEditCategoryParent() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['parent.']))
			return true;
		return false;
	}
	function isAllowedToEditCategorySharedUser() {
		if ($this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] ['edit.'] ['category.'] ['fields.'] ['sharedUser.']))
			return true;
		return false;
	}
	function isAllowedToDeleteCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['category.']);
	}
	function isAllowedToDeleteOnlyOwnCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['category.'] ['onlyOwnCategory.']);
	}
	function isAllowedToDeleteGeneralCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['category.'] ['generalCategory.']);
	}
	function isAllowedToDeletePublicCategory() {
		return $this->checkRights ($this->conf ['rights.'] ['delete.'] ['category.'] ['publicCategory.']);
	}
	function isAllowedToConfigure() {
		return ($this->isLoggedIn () && $this->isViewEnabled ('admin') && ($this->isCalAdmin () || $this->isAllowedToCreateCalendar () || $this->isAllowedToEditCalendar () || $this->isAllowedToDeleteCalendar () || $this->isAllowedToCreateCategory () || $this->isAllowedToEditCategory () || $this->isAllowedToDeleteCategory () || $this->isAllowedTo ('create', 'location') || $this->isAllowedTo ('edit', 'location') || $this->isAllowedTo ('delete', 'location') || $this->isAllowedToCreateOrganizer () || $this->isAllowedToEditOrganizer () || $this->isAllowedToDeleteOrganizer ()));
	}
	function isAllowedTo($type, $object, $field = '') {
		$field = strtolower ($field);
		if ($field == '') {
			return $this->checkRights ($this->conf ['rights.'] [$type . '.'] [$object . '.']);
		} else if ($field == 'teaser' && ! $this->confArr ['useTeaser']) {
			return false;
		}
		
		if (($this->conf ['rights.'] [$type . '.'] ['public'] && $this->conf ['rights.'] [$type . '.'] [$object . '.'] ['fields.'] [$field . '.'] ['public']) || $this->checkRights ($this->conf ['rights.'] [$type . '.'] [$object . '.'] ['enableAllFields.']) || $this->checkRights ($this->conf ['rights.'] [$type . '.'] [$object . '.'] ['fields.'] [$field . '.'])) {
			return true;
		}
		
		return false;
	}
	function checkRights($category) {
		if ($this->isCalAdmin ()) {
			return true;
		}
		if ($this->isLoggedIn ()) {
			$users = explode (',', $category ['user']);
			$groups = explode (',', $category ['group']);
			
			if (array_search ($this->getUserId (), $users) !== false)
				return true;
			$userGroups = $this->getUserGroups ();
			foreach ($groups as $key => $group) {
				if (array_search (ltrim ($group), $userGroups) !== false)
					return true;
			}
		}
		if ($category ['public'] == 1) {
			return true;
		}
		return false;
	}
	function checkView($view) {
		if ($view == 'day' || $view == 'week' || $view == 'month' || $view == 'year' || $view == 'event' || $view == 'todo' || $view == 'location' || $view == 'organizer' || $view == 'list' || $view == 'icslist' || $view == 'search_all' || $view == 'search_event' || $view == 'search_location' || $view == 'search_organizer') {
			// catch all allowed standard view types
		} else if (($view == 'ics' || $view == 'single_ics') && $this->conf ['view.'] ['ics.'] ['showIcsLinks'] && $this->isViewEnabled ($view)) {
			$this->conf ['view.'] ['allowedViews'] = array (
					0 => $view 
			);
			return $view;
		} else if ($view == 'rss') {
			$this->conf ['view.'] ['allowedViews'] = array (
					0 => $view 
			);
			return $view;
		} else if ($view == 'subscription' && $this->conf ['allowSubscribe'] && $this->isViewEnabled ($view)) {
		} else if ($view == 'translation' && $this->rightsObj->isAllowedTo ('create', 'translation') && $this->isViewEnabled ($view)) {
		} else if ($view == 'meeting' && $this->isViewEnabled ($view)) {
		} else if ($view == 'admin' && $this->rightsObj->isAllowedToConfigure ()) {
		} else if (($view == 'load_events' || $view == 'load_todos' || $view == 'load_calendars' || $view == 'load_categories' || $view == 'load_rights' || $view == 'load_locations' || $view == 'load_organizers' || $view == 'search_user_and_group') && $this->conf ['view.'] ['enableAjax']) {
			// catch all allowed standard view types
		} else if (($view == 'save_calendar' || $view == 'edit_calendar' || $view == 'confirm_calendar' || $view == 'delete_calendar' || $view == 'remove_calendar' || $view == 'create_calendar') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateCalendar () || $this->rightsObj->isAllowedToEditCalendar () || $this->rightsObj->isAllowedToDeleteCalendar ())) {
		} else if (($view == 'save_category' || $view == 'edit_category' || $view == 'confirm_category' || $view == 'delete_category' || $view == 'remove_category' || $view == 'create_category') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateCalendar () || $this->rightsObj->isAllowedToEditCategory () || $this->rightsObj->isAllowedToDeleteCategory ())) {
		} else if (($view == 'save_event' || $view == 'edit_event' || $view == 'confirm_event' || $view == 'delete_event' || $view == 'remove_event' || $view == 'create_event') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateEvent () || $this->rightsObj->isAllowedToEditEvent () || $this->rightsObj->isAllowedToDeleteEvents ())) {
		} else if (($view == 'save_exception_event' || $view == 'edit_exception_event' || $view == 'confirm_exception_event' || $view == 'delete_exception_event' || $view == 'remove_exception_event' || $view == 'create_exception_event') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateExceptionEvent () || $this->rightsObj->isAllowedToEditExceptionEvent () || $this->rightsObj->isAllowedToDeleteExceptionEvents ())) {
		} else if (($view == 'save_location' || $view == 'confirm_location' || $view == 'create_location' || $view == 'edit_location' || $view == 'delete_location' || $view == 'remove_location') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateLocations () || $this->rightsObj->isAllowedToEditLocation () || $this->rightsObj->isAllowedToDeleteLocation ())) {
			// catch create_location view type and check all conditions
		} else if (($view == 'save_organizer' || $view == 'confirm_organizer' || $view == 'create_organizer' || $view == 'edit_organizer' || $view == 'delete_organizer' || $view == 'remove_organizer') && $this->rightsObj->isCalEditable () && ($this->rightsObj->isAllowedToCreateOrganizer () || $this->rightsObj->isAllowedToEditOrganizer () || $this->rightsObj->isAllowedToDeleteOrganizer ())) {
			// catch create_organizer view type and check all conditions
			// I'm not sure why this is in here, but I think it shouldn't, b/c you will get an empty create_event view even if you are not allowed to create events
			// } else if ($this->isViewEnabled($view)){
		} else {
			// a not wanted view type -> convert it
			$view = $this->conf ['view.'] ['allowedViews'] [0];
			if ($view == '') {
				$view = 'month';
			}
			$this->conf ['type'] = '';
			$this->controller->piVars ['type'] = null;
		}
		if (count ($this->conf ['view.'] ['allowedViews']) == 1) {
			$view = $this->conf ['view.'] ['allowedViews'] [0];
			if (! in_array ($this->conf ['view.'] ['allowedViews'] [0], array (
					'event',
					'organizer',
					'location' 
			))) {
				$this->conf ['uid'] = '';
				$this->piVars ['uid'] = null;
				$this->conf ['type'] = '';
				$this->piVars ['type'] = null;
			} else if ($this->conf ['view.'] ['allowedViews'] [0] == 'event' && (($this->piVars ['view'] == 'location' && ! in_array ('location', $this->conf ['view.'] ['allowedViews'])) || ($this->piVars ['view'] == 'organizer' && ! in_array ('organizer', $this->conf ['view.'] ['allowedViews'])))) {
				return;
			}
		} else if (! ($view == 'admin' && $this->rightsObj->isAllowedToConfigure ()) && ! in_array ($view, $this->conf ['view.'] ['allowedViews'])) {
			$view = $this->conf ['view.'] ['allowedViews'] [0];
		}
		if (! $view) {
			$view = $this->conf ['view.'] ['allowedViews'] [0];
		}
		return $view;
	}
	
	/* @todo Is there a way to check for allowed views on other pages that are specified by TS? */
	function isViewEnabled($view) {
		if (in_array ($view, $this->conf ['view.'] ['allowedViewsToLinkTo'])) {
			return true;
		}
		return false;
	}
	
	/**
	 * Sets the default pages for saving calendars, events, etc.
	 * If the Typoscript
	 * is not set and there's only one page in the pidList, then we can set this
	 * page be default.
	 *
	 * @return none
	 */
	function setDefaultSaveToPage() {
		$pagesArray = explode (",", $this->conf ['pidList']);
		
		/* If there's only one page in pidList */
		if (count ($pagesArray) == 1) {
			$pid = $pagesArray [0];
			
			/* If a saveTo page does not have a value set, set a default */
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['calendar.'] ['saveCalendarToPid'], $pid);
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['category.'] ['saveCategoryToPid'], $pid);
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['event.'] ['saveEventToPid'], $pid);
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['exceptionEvent.'] ['saveExceptionEventToPid'], $pid);
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['location.'] ['saveLocationToPid'], $pid);
			$this->setPidIfEmpty ($this->conf ['rights.'] ['create.'] ['organizer.'] ['saveOrganizerToPid'], $pid);
		}
	}
	
	/**
	 * Sets a conf value if it is currently empty.
	 * Helper function for setDefaultSaveToPage().
	 *
	 * @param
	 *        	mixed		The conf value to be set.
	 * @param
	 *        	mixed		The value to set.
	 */
	function setPidIfEmpty(&$conf, $value) {
		if (! $conf) {
			$conf = $value;
		}
	}
}

?>