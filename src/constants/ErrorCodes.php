<?php

namespace Constants;

class ErrorCodes
{
	// Error code categories
	public const string APP = 'APP';
	public const string ROUTER = 'ROUTER';
	public const string CHANNELS = 'CHANNELS';
	public const string TOUR_LIST = 'TOUR_LIST';
	public const string TOUR_VIEW = 'TOUR_VIEW';
	public const string TOUR_CHECK_AVAILABILITY = 'TOUR_CHECK_AVAILABILITY';
	public const string TOUR_DEPARTURES = 'TOUR_DEPARTURES';
	public const string TOUR_START_BOOKING = 'TOUR_START_BOOKING';
	public const string TOUR_COMMIT_BOOKING = 'TOUR_COMMIT_BOOKING';
	public const string BOOKINGS = 'BOOKINGS';
	public const string CUSTOMERS = 'CUSTOMERS';

	// APP Error Keys
	public const string INCORRECT_ROUTE_ACTION = 'INCORRECT_ROUTE_ACTION';
	public const string LOGIN_REQUIRED = 'LOGIN_REQUIRED';

	// ROUTER Error Keys
	public const string ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';
	public const string MISSING_ROUTE_DATA = 'MISSING_ROUTE_DATA';

	// CHANNELS Error Keys
	public const string NO_CHANNEL_DATA = 'NO_CHANNEL_DATA';
	public const string SESS_NO_CHANNEL_ID = 'SESS_NO_CHANNEL_ID';
	public const string POST_NO_CHANNEL_ID = 'POST_NO_CHANNEL_ID';

	// TOUR_LIST Error Keys
	public const string NO_CHANNEL_TOUR_DATA = 'NO_CHANNEL_TOUR_DATA';
	public const string POST_NO_TOUR_ID = 'POST_NO_TOUR_ID';

	// TOUR_VIEW Error Keys
	public const string NO_TOUR_DATA = 'NO_TOUR_DATA';
	public const string POST_NO_TOUR_ID_VIEW = 'POST_NO_TOUR_ID_VIEW';
	public const string POST_NO_VALID_TOUR_BOOKING_DETAILS = 'POST_NO_VALID_TOUR_BOOKING_DETAILS';
	public const string POST_NO_VALID_TOUR_BOOKING_RATES = 'POST_NO_VALID_TOUR_BOOKING_RATES';
	public const string SESS_NO_CHANNEL_OR_TOUR_ID = 'SESS_NO_CHANNEL_OR_TOUR_ID';

	// TOUR_CHECK_AVAILABILITY Error Keys
	public const string EMPTY_TOUR_BOOKING_DATA = 'EMPTY_TOUR_BOOKING_DATA';
	public const string SESS_NO_CHANNEL_OR_TOUR_ID_CHECK_AVAILABILITY = 'SESS_NO_CHANNEL_OR_TOUR_ID';
	public const string NO_AVAILABLE_COMPONENTS = 'NO_AVAILABLE_COMPONENTS';

	// TOUR_DEPARTURES Error Keys
	public const string POST_EMPTY_DEPARTURE_CUSTOMER_DATA = 'POST_EMPTY_DEPARTURE_CUSTOMER_DATA';
	public const string POST_ERROR_SAVING_COMPONENT_KEY = 'POST_ERROR_SAVING_COMPONENT_KEY';

	// TOUR_START_BOOKING Error Keys
	public const string ERROR_CREATING_TEMPORAL_BOOKING = 'ERROR_CREATING_TEMPORAL_BOOKING';

	// TOUR_COMMIT_BOOKING Error Keys
	public const string ERROR_COMMITTING_BOOKING = 'ERROR_COMMITTING_BOOKING';

	// BOOKINGS Error Keys
	public const string NO_BOOKINGS_DATA = 'NO_BOOKINGS_DATA';
	public const string POST_NO_BOOKING_ID = 'POST_NO_BOOKING_ID';
	public const string POST_NO_MATCHING_STORED_BOOKING_ID = 'POST_NO_MATCHING_STORED_BOOKING_ID';

	// CUSTOMERS Error Keys
	public const string NO_CUSTOMERS_DATA = 'NO_CUSTOMERS_DATA';
	public const string POST_ERROR_UPDATING_CUSTOMER = 'POST_ERROR_UPDATING_CUSTOMER';
	public const string POST_NO_CUSTOMER_ID = 'POST_NO_CUSTOMER_ID';
	public const string POST_NO_MATCHING_STORED_CUSTOMER_ID = 'POST_NO_MATCHING_STORED_CUSTOMER_ID';
}
