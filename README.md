# Dashboard Console

The dashboard console is a command line client for querying the PNX Dashboard API.

## Installation

Install using composer:

`composer install`

## Running the commands

The client has two commands, one for viewing a list of all snapshots, and the
other for viewing the detail of an individual site.

### Common parameters

* `--base-url` The base url for the Dashboard API.
* `--username` The username used to connect to the dashboard.
* `--password` The password used to connect to the dashboard.

## View all snapshots

To view all snapshots, run the command:

`./dashboard.php snapshots --password <SECRET PASSWORD>`

To filter to show only snapshots which have _error_ alerts. Add the flag:

`--alert-level=error`

## View snapshot details

To view the details of a snapshot, run the command:

`./dashboard.php snapshot --site-id=<SITE ID> --password <SECRET PASSWORD>`

Where `<SITE ID>` is the unique site ID. This is displayed in the _snapshots_
output.


