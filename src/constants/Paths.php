<?php

namespace Constants;

class Paths
{
	// General paths
	public const string LOGIN = '/login/';
	public const string ERROR = '/error/';
	public const string DASHBOARD = '/dashboard/';
	public const string BOOKINGS = '/bookings/';

	// Tour paths
	public const string TOUR_LIST = '/tourList/';
	public const string TOUR_VIEW = '/tourList/tourView/';
	public const string CHECK_TOUR_AVAILABILITY = '/tourList/tourView/checkTourAvailability/';

	// Booking process paths
	public const string CREATE_BOOKING = '/tourList/tourView/createBooking/';
	public const string BOOKING_CONFIRMATION = '/tourList/tourView/bookingConfirmation/';
}