<?php

use Layla\API;

/**
* 
*/
class Admin_Page_Controller extends Admin_Base_Controller
{
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function get_read_multiple()
	{
		// Set API options
		$options = array(
			'offset' => (Input::get('page', 1) - 1) * $this->per_page,
			'limit' => $this->per_page,
			'sort_by' => Input::get('sort_by', 'meta_title'),
			'order' => Input::get('order', 'ASC')
		);

		// Add search to API options
		if(Input::has('q'))
		{
			$options['search'] = array(
				'string' => Input::get('q'),
				'columns' => array(
					'menu', 
					'meta_title',
					'content'
				)
			);
		}

		// Get the Pages
		$pages = API::get(array('pages'), $options);
		
		// Paginate the Pages
		$pages = Paginator::make($pages->get('results'), $pages->get('total'), $this->per_page);

		$this->layout->content = Artifact::page('page.read_multiple')
			->with('pages', $pages);
	}

	public function get_create()
	{
		// Get Layouts and put it in a nice array for the dropdown
		$layouts = model_array_pluck(API::get(array('layout', 'all'))->get('results'), function($layout)
		{
			return $layout->name;
		}, 'id');

		$this->layout->content = Artifact::page('page.create')
			->with('layouts', $layouts);
	}

	public function post_create()
	{
		$response = API::post(array('page'), Input::all());
		// Error were found our data! Redirect to form with errors and old input
		if( ! $response->success)
		{
			// Errors were found on our data! Redirect to form with errors and old input
			if($response->code == 400)
			{
				return Redirect::to(prefix('admin').'page/add')
							 ->with('errors', new Messages($response->get()))
					   ->with_input();
			}

			return Event::first($response->code);
		}

		// Add success notification
		Notification::success('Successfully created page');

		return Redirect::to(prefix('admin').'pages');
	}

	public function get_translate($slug = null, $language_slug = null)
	{
		if( ! is_null($slug))
		{
			$response = API::get(array('page', $slug));
			
			if($response->code !== 200)
			{
				return Event::first($response->code);
			}

			$page = $response->get();
		}

		if( ! is_null($language_slug))
		{
			$response = API::get(array('language', $language_slug));
			
			if($response->code !== 200)
			{
				return Event::first($response->code);
			}

			$language = $response->get();
		}

		// Get Layouts and put it in a nice array for the dropdown
		$layouts = model_array_pluck(API::get(array('layouts'))->get('results'), function($layout)
		{
			return $layout->name;
		}, 'id');

		$this->layout->content = Artifact::page('page.translate')
			->with('id', $page->id)
			->with('language', $language)
			->with('layouts', $layouts);
	}

	public function get_update($slug = null)
	{
		$options = array();

		// Get the Page
		$response = API::get(array('page', $slug), $options);

		// Handle response codes other than 200 OK
		if( ! $response->success)
		{
			return Event::first($response->code);
		}

		// The response body is the Page
		$page = $response->get();

		$response = API::get(array('language', $page->lang->language_id));
		
		if($response->code !== 200)
		{
			return Event::first($response->code);
		}

		$language = $response->get();

		// Get Layouts and put it in a nice array for the dropdown
		$layouts = model_array_pluck(API::get(array('layouts'))->get('results'), function($layout)
		{
			return $layout->name;
		}, 'id');

		$this->layout->content = Artifact::page('page.update')
			->with('language', $language)
			->with('page', $page);
	}

	public function put_update($slug = null)
	{
		// Update the Page
		$response = API::put(array('page', $slug), Input::all());

		// Handle response codes other than 200 OK
		if( ! $response->success)
		{
			// Errors were found on our data! Redirect to form with errors and old input
			if($response->code == 400)
			{
				return Redirect::to(prefix('admin').'page/edit/' . $slug)
					->with('errors', new Messages($response->get()))
					->with_input();
			}

			return Event::first($response->code);
		}

		// Add success notification
		Notification::success('Successfully updated page translation');

		return Redirect::to(prefix('admin').'pages');
	}

	public function put_translate($id = null, $language_slug = null)
	{
		// Translate the Page
		$response = API::put(array('page', $id), Input::all());

		// Handle response codes other than 200 OK
		if( ! $response->success)
		{
			// Errors were found on our data! Redirect to form with errors and old input
			if($response->code == 400)
			{
				return Redirect::to(prefix('admin').'page/translate/'.$id.'/'.$language_slug)
					->with('errors', new Messages($response->get()))
					->with_input();
			}

			return Event::first($response->code);
		}

		// Add success notification
		Notification::success('Successfully translated page');

		return Redirect::to(prefix('admin').'pages');
	}

	public function delete_delete($slug = null)
	{
		// Delete the Page
		$response = API::delete(array('page', $slug));

		// Handle response codes other than 200 OK
		if( ! $response->success)
		{
			return Event::first($response->code);
		}

		// Add success notification
		Notification::success('Successfully deleted page');

		return Redirect::to(prefix('admin').'pages');
	}

}