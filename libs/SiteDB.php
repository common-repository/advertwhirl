<?php
/*  Copyright 2010
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists("SiteDB")){


	class SiteDB {

		/** Return array of user ids associated with a given role
		  * via http://sltaylor.co.uk/blog/get-wordpress-users-by-role/
		  */
		public function GetUserIdsByRole($role){
			$ids = null;
			if(class_exists('WP_User_Query')){
				error_log("WP_User_Query");
				$wp_user_search = new WP_User_Query( array(
				                                           'role' => $role,
														   'orderby' => 'user_lastname, user_firstname',
														   'fields' => 'ID') );
				$ids = $wp_user_search->get_results();
			}else if(class_exists('WP_User_Search')){
				error_log("WP_User_Search");
				$wp_user_search = new WP_User_Search('', '', $role);
				$ids = $wp_user_search->get_results();
			}else{
				global $wpdb;
				$sql = '
					SELECT ID
					FROM '.$wpdb->users.' INNER JOIN '.$wpdb->usermeta.'
					ON '.$wpdb->users.'.ID = '.$wpdb->usermeta.'.user_id
					WHERE '.$wpdb->usermeta.'.meta_key = \''.$wpdb->prefix.'capabilities\'
					AND '.$wpdb->usermeta.'.meta_value LIKE \'%"'.$role.'"%\'
					ORDER BY '.$wpdb->usermeta.'user_lastname, '.$wpdb->usermeta.'user_firstname';

				error_log($sql);

				$ids = $wpdb->get_col($sql);
			}
			return $ids;
		}

		/** Return an array of authors
		  * via http://cogdogblog.com/2010/08/05/wordpress-authors-list/
		  */
		public function GetAuthorsList(){
			global $wpdb;

			$authors = array(); // this is cheap, a holder for author data

			// get array of all author ids for a role
			$ids = $this->GetUserIdsByRole('author');

			foreach($ids as $id){
				// load info on this user
				$author = get_userdata($id);

				// store output in temp array; we use last names as an index in this array
				$authors[$id]['lastname'] = $author->user_lastname;
				$authors[$id]['firstname'] = $author->user_firstname;
				$authors[$id]['login'] = $author->user_login;
				$authors[$id]['displayname'] = $author->display_name;
			}

			return $authors;
		}

		/** Return an array of a posts category names
		  *
		  */
		public function GetPostCategoryNames($id = null){
			if(!isset($id))
				$id = $wp_query->post->ID;

			$categories = wp_get_post_categories($id);
			$names = array();

			foreach($categories as $cid){
				$cat = get_category($cid);
				$names[] = $cat->name;
			}
			return $names;
		}

		/** Return an array of a post's tags by field
		  * returns null on error or empty sets
		  */
		public function GetPostTagFields($id, $field){
			if(!isset($id) || !isset($field))
				return null;

			$tags = wp_get_post_tags($id);
			$fields = array();

			foreach($tags as $tag){
				if(isset($tag[$field]))
					$fields[] = $tag[$field];
			}
			if(count($fields) > 0)
				return $fields;

			return null;
		}

		/** Return an array of a post's tags names
		  *
		  */
		public function GetPostTagNames($id = null){
			if(!isset($id))
				$id = $wp_query->post->ID;

			return $this->GetPostTagFields($id, 'name');
		}

		/** Return an array of a post's tags slugs
		  *
		  */
		public function GetPostTagSlugs($id = null){
			if(!isset($id))
				$id = $wp_query->post->ID;

			return $this->GetPostTagFields($id, 'name');
		}

		/** Return an array of a post's tags taxonomy
		  *
		  */
		public function GetPostTagTaxonomy($id = null){
			if(!isset($id))
				$id = $wp_query->post->ID;

			return $this->GetPostTagFields($id, 'taxonomy');
		}

		/** Return an array of a post's tags parents
		  *
		  */
		public function GetPostTagParents($id = null){
			if(!isset($id))
				$id = $wp_query->post->ID;

			return $this->GetPostTagFields($id, 'name');
		}

	}

}
