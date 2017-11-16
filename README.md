# API Healthcare plugin for Craft CMS

This plugin allows [Craft](http://craftcms.com) websites to search and display open positions from a company's [API Healthcare](http://www.apihealthcare.com/) (now GE Healthcare) account using the ClearConnect 2.0 API.


## Installation

To install the API Healthcare plugin, follow these steps:

1.  Upload the apihealthcare folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the API Healthcare plugin.
3.  Navigate to the API Healthcare plugin settings page, and configure the account settings.
4.  Navigate to API Healthcare -> Professions and click "Update Professions".
5.  Navigate to API Healthcare -> Specialties and click "Update Specialties".
6.  Navigate to API Healthcare -> Per-Diem Clients to add whitelisted clients.
7.  Navigate to API Healthcare -> Locations, click "Populate States" and then "Edit Search Options" to set whitelisted locations.
8.  Navigate to API Healthcare -> Jobs, click "Update Jobs" to populate available jobs for search.
9.  Add Cron job to periodically refresh cache of available jobs. For example:

    0 0,6,12,18 0 0 0 /usr/bin/wget http://mysite.com/actions/apiHealthcare/jobs/triggerUpdate

## Changelog

### 2.1.0

* Added getJobData() function to output json data of available jobs, professions, specialties & locations for frontend search filtering.

### 2.0.0

* Search from local cache of available whitelisted jobs instead of hitting up API Healthcare for every request
* Job search forms now only list available Professions, Specialties and Locations instead of the entire whitelist for each to improve search experience

### 1.0.1

* Fixed a bug where saving entries containing a Job Listing fieldtype would throw a PHP exception

### 1.0.0

* Refactored job search functions for better clarity & composability
* Added API Healthcare Job Listing fieldtype to allow custom listings on non-search pages

### 0.5.3

* Updated to return full name of state for descriptions of search parameters
* Updated API Healthcare's ClearConnectLib to allow api calls from unsecure domain
* Updated jobSearchTitle() function to return formatted string describing search parameters for meta titles or heading titles

### 0.4.2

* Added youSearchedFor() function to return formatted string describing search parameters

### 0.4.1

* Only show transportationNote or housingNote for ltOrders

### 0.4.0

* Added whitelist for Locations in CP
* Refactored Options into separate Professions, Specialties & Per-Diem Clients classes for better separation of concerns

### 0.3.1

* Implemented whitelists in search results
* Disabled ability to change Profession and Specialty names as they are used to filter search results

### 0.3.0

* Added whitelist for Professions in CP
* Added whitelist for Specialties in CP
* Added whitelist for Per-Diem Clients in CP
* Added Long-Term orders to search results
* Need to implement whitelists in search results

### 0.2.0

* Plugin caches Profession and Specialty search options to database for faster load times
* Fixed issue where certain Profession and Specialty options with special characters were breaking search

### 0.1.1

* Added ability to search by Zip Code

### 0.1.0

* Initial release
