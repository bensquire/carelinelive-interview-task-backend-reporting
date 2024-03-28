# CareLineLive Interview Test

## Introduction

Thanks for taking the time to complete this test; it will help us evaluate your ability and approach to
problem-solving.

We appreciate that your time is valuable, so we have tried to keep the test as short as possible. We're not too
concerned with completeness, as long as you can demonstrate an appropriate understanding of the problem and a good
approach to solving it.

This repository has been set up with a bare bones Laravel 11 application that should let you get started quickly. It's
configured to use SQLite, but feel free to change to either MySQL or Postgres. You have free reign over the design and
implementation of the solution, and feel free to make modifications to any existing code.

Please include a description of any features that you intended to implement but did not have time to complete, including
a high-level explanation of they would have been implemented.

### Testing

We expect an appropriate level of testing to be included with your submission. Currently the application is configured
to use PHPUnit for testing, though you may switch to [Pest](https://pestphp.com/) if you prefer:

```shell
composer require pestphp/pest pestphp/pest-plugin-laravel pestphp/pest-plugin-drift --dev --with-all-dependencies
./vendor/bin/pest --drift
```

You can run the tests using:
```shell
php artisan test
```

### Submission

Please submit your solution as a fork of this repository. Once you're ready, please send us a link to your fork. You may
either publish it publicly or privately, however please ensure that we have access to view private repositories.

---

## The problem

You have been asked to create a simple application that will allow users to analyse attendance for home care visits.

Users should be able to calculate the following information:

- The total number of visits for a given period (day, week, month, year)
- Punctuality of visits (on time, early, late, missed), with configurable thresholds
- The average duration of visits for a given period
- Notice given when visits are cancelled (amount of time before the planned start time)
- Distribution within a given period of:
    - Visit start times
    - Visit durations
    - Delivery status
    - Visit punctuality

Users should also be able to filter the data by:

- Type of visit (e.g. medication, personal care, meal preparation)
- Delivery status (e.g. completed, cancelled, rescheduled)
- Punctuality (e.g. on time, late, missed)
- Visit duration (e.g. less than 30 minutes, more than 1 hour)

### Out of scope
You are not expected to implement the following:

- Authentication and authorisation
- User interface (web or API)

### Bonus points

- Implement a API or UI to allow users to interact with the data
- Export results to a CSV file
- Geospatial analysis of arrival and departure locations against the client's location

---

## Set up local environment

Ensure you have the following dependencies installed on your local machine:

- PHP 8.2+

Configure the application for local development using the following commands:

```shell
cp .env.example .env
php artisan key:generate
```

## Running locally

### Web

```shell
php artisan serve
```

### Tests

```shell
php artisan test
```
