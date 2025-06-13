<?php
namespace Helpers;

require_once __DIR__ . '/../../config/config.php';

class TourRenderHelper 
{
	private $tourCMS;
	private $channelID;
	private $tours_per_page;
	private $current_page;
	private $searchToursResult;

	/**
	 * TourRenderHelper constructor.
	 *
	 * @param object $tourCMS An instance of the TourCMS class.
	 * @param int $channelID The ID of the channel.
	 * @param int $tours_per_page The number of tours to display per page.
	 */
	public function __construct($tourCMS, $channelID, $tours_per_page ) 
	{
		$this->tourCMS = $tourCMS;
		$this->channelID = $channelID;
		$this->tours_per_page = $tours_per_page;
		$this->current_page = isset( $_GET['page'] ) ? (int) $_GET['page'] : 1;
		$this->searchToursResult = null;
	}

	/**
	 * Builds a query string for its use on the TourCMS API request.
	 *
	 * This method constructs a query string based on the number of tours per page
	 * and the current page number. The page number is retrieved from the GET request.
	 *
	 * @return string The query string for pagination.
	 */
	private function buildQuery() 
	{
		// Set a querystring for the search
		$query_parameters = array(
			"per_page" => $this->tours_per_page,
			"page" => $this->current_page,
			"product_type" => 4,
			"country" => 'ES'
		);

		$querystring = http_build_query($query_parameters);

		return $querystring;
	}

	/**
	 * Calculates the total number of pages based on the total number of tours and tours per page.
	 *
	 * This method retrieves the total number of tours from the search result and calculates
	 * the total number of pages by dividing the total number of tours by the number of tours
	 * per page, rounding up to the nearest whole number.
	 *
	 * @param object $search_result The search result object containing the total tour count.
	 * @return int The total number of pages.
	 */
	private function getTotalPages() 
	{
		$total_tours 	= $this->searchToursResult->total_tour_count;
		$total_pages 	= ceil( $total_tours / $this->tours_per_page );

		return $total_pages;
	}

	/**
	 * Searches for tours or hotels using the TourCMS API.
	 *
	 * This method builds a query string using the `buildQuery` method and then
	 * calls the TourCMS API to search for tours or hotels based on the query.
	 *
	 * @return mixed The result of the search from the TourCMS API.
	 */
	public function searchTours() 
	{
		$querystring = $this->buildQuery();
		// Call the TourCMS API method to search for Tours/Hotels
		$searchToursResult = $this->tourCMS->search_tours( $querystring, $this->channelID );

		return $searchToursResult;
	}

	/**
	 * Retrieves the search results for tours.
	 *
	 * @return array The search results for tours.
	 */
	public function getTours() 
	{
		// Check if the search result is null, if so, perform the search
		if ( $this->searchToursResult === null ) {
			$this->searchToursResult = $this->searchTours();
		}
		
		return $this->searchToursResult;
	}

	/**
	 * Generates and outputs the HTML for displaying the current page and total number of pages.
	 *
	 * @param int $current_page The current page number.
	 * @param int $total_pages The total number of pages.
	 * @return void
	 */
	public function renderCurrentPageInfo()
	{
		$total_pages = $this->getTotalPages();
		echo '<p class="page_info">Page <strong>' . $this->current_page . '</strong> of <strong>' . $total_pages .'</strong></p>';
	}

	/**
	 * Renders pagination links for navigating between pages.
	 *
	 * @param int $current_page The current page number.
	 * @param int $total_pages The total number of pages.
	 * @return void
	 *
	 * This function outputs HTML for pagination links. It includes links to the first page,
	 * previous page, next page, and last page based on the current page and total pages.
	 * If the current page is greater than 1, it shows links to the first and previous pages.
	 * If the current page is less than the total pages, it shows links to the next and last pages.
	 */
	public function renderPagination() 
	{
		$total_pages = $this->getTotalPages();
		echo '<div class="pagination">';

			if ( $this->current_page > 1 ) {
				// Go to the first page
				echo '<a href="?page=1">&lt;&lt; First page</a>';
				// Go to the previous page
				echo '<div class="pagination_central"><a href="?page=' . ( $this->current_page - 1 ) . '">&lt; Previous page</a>';
			}

			if ( $this->current_page < $total_pages ) {
				// Next page
				echo '<a href="?page=' . ( $this->current_page + 1 ) . '">Next page &gt;</a></div>';
				// Last page
				echo '<a href="?page=' . $total_pages . '">Last page &gt;&gt;</a>';
			}

		echo '</div>';
	}

	/**
	 * Renders the HTML for displaying a list of tours.
	 *
	 * @param array $tours An array of tours to be displayed.
	 * @return void
	 */
	public function renderTourData( $searchToursResult )
	{
		foreach ( $searchToursResult->tour as $tour ) {
			
			// Get the tourID
			$tourID = $tour->tour_id;

			// If we have no channel ID configured in our config
			// (i.e. we are an agent) we also need to pass the Channel ID
			if ( $this->channelID == 0 ) {
				$this->channelID = $tour->channel_id;
			}

			// Check if the tour ID or channel ID is invalid and skip the tour if either is zero.
			if ( $tourID == 0 || $this->channelID == 0 ) {
				echo '<p>Invalid tour or channel ID. Skipping tour...</p>';
				continue;
			}

			// Check if the tour has a name, URL, or image before rendering.
			if ( !empty( $tour->tour_name ) || !empty( $tour->tour_url ) || !empty( $tour->image ) ) {
				$tourHTML = '<div class="tour_container">';
					$tourHTML .= '<div class="tour_container-image">';
						// Show the tour image or thumbnail.
						$tourImageURL = !empty( $tour->image ) ? $tour->image : $tour->thumbnail_image;
						$tourHTML .= '<a href="' . $tour->tour_url . '" target="_blank">
										<img src="' . $tourImageURL . '" alt="' . $tour->tour_name . '">
									</a>';
					$tourHTML .= '</div>';

					$tourHTML .= '<div class="tour_container-contents">';
						// Tour name.
						$tourHTML .= '<h3>' . $tour->tour_name_long . '</h3>';
						// Tour tags.
						$tourHTML .= '<div class="tour_container-contents_tags">';
							$tourHTML .= '<a href="#"><i class="fa-solid fa-location-dot"></i>' . $tour->location . '</a>';
							$tourHTML .= '<a href="#"><i class="fa-solid fa-bolt"></i>' . $tour->available . '</a>';
						$tourHTML .= '</div>';
						// Tour summary.
						$tourHTML .= '<p class="tour_container-contents_summary">' . $tour->summary . '</p>';
						// Tour short description.
						$tourHTML .= '<p class="tour_container-contents_shortdesc">' . $tour->shortdesc . '</p>';
					$tourHTML .= '</div>';

					// Tour footer
					$tourHTML .= '<div class="tour_container-footer">';
						// Tour duration.
						$tourHTML .= '<div class="tour_container-footer_duration"><i class="fa-solid fa-clock-rotate-left"></i><span>Duration: </span>' . $tour->duration_desc . '</div>';
						// Tour price and link.
						$tourHTML .= '<a class="tour_container-footer_link" href="' . $tour->book_url . '" target="_blank">Book now from ' . $tour->from_price_display . '</a>';
					$tourHTML .= '</div>';
				$tourHTML .= '</div>';

				echo $tourHTML;
			} else {
				echo '<p>Missing required tour information. Skipping tour...</p>';
				continue;
			}
		}
	}
}
?>