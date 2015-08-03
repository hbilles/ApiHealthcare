# API Healthcare plugin for Craft CMS

This plugin allows [Craft](http://buildwithcraft.com) websites to search and display open positions from a company's [API Healthcare](http://www.apihealthcare.com/) account using the ClearConnect 2.0 API.


## Installation

To install Perform, follow these steps:

1.  Upload the apihealthcare folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the API Healthcare plugin.
3.  Navigate to the API Healthcare plugin settings page, and configure the account settings.
4.  Navigate to API Healthcare -> Professions and click "Update Professions".
5.  Navigate to API Healthcare -> Specialties and click "Update Specialties".
6.  Navigate to API Healthcare -> Per-Diem Clients to add whitelisted clients.
7.  Navigate to API Healthcare -> Locations, click "Populate States" and then "Edit Search Options" to set whitelisted locations.

## Changelog

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
