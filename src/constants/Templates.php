<?php

namespace Constants;

class Templates
{
	// Session
	public const string LOGOUT = '_common/logoutPage';
	public const string LOGIN = 'login/loginPage';

	// Error
	public const string ERROR = '_common/error/errorPage';

	// Tours
	public const string TOUR_LIST_PAGE = 'tours/tourListPage';
	public const string TOUR_VIEW_PAGE = 'tours/tourViewPage';

	// Channels
	public const string CHANNEL_LIST_PAGE = 'channels/channelListPage';

	// Bookings
	public const string BOOKINGS_LIST = 'bookings/bookingsListPage';
	public const string BOOKING_CONFIRMATION = 'bookings/bookingConfirmation';

	// Customers
	public const string CUSTOMER_LIST = 'customers/customerListPage';
	public const string CUSTOMER_EDIT = 'customers/customerEditPage';
	public const string CUSTOMER_UPDATE_CONFIRMATION = 'customers/customerUpdateConfirmation';
}