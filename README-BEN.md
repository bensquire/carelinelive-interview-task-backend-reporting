# CareLineLive Interview Test - Bens Notes

## Phase 1

This lists (briefly) what I did to build out the API, prior to our call on 3rd April 2023

### Completed

- Workout how to get seed data into DB
    - Simple data first
    - Now work out how to populate care-visit specific fields
        - Random status with bias towards 'pending' and 'delivered'
- Setup ability to get data via simple API
- Create first API controller, using eloquent to query for data
- Create validation middleware for first API endpoint
- Create feature test for validation middleware
- Create feature test for API controller

### Proposed improvements

- The remaining functionality laid out in the spec?
- Static Analysis?
- PHPCSFixer?
- Additional unit/feature tests
- Code coverage
- Performance testing
    - Additional data needed
    - Is doing the calculation in DB the correct way?
- How is the rawSQL (sqlite) I've put in going to work on a production system? Ideas:
    - Conditional database specific code?
    - DB views to calculate and present the final data?
    - Abstract the DB calls into a service provide
    - Ideally use the same DB locally as prod
- Not convinced I've got the missed punctuality calculation right, feels like it should be a state.

### API Endpoints

#### Total Visits:

```shell
curl -G 'http://localhost:8000/api/total-visits?year=2024&month=4&day=2'
```

#### Average Duration:

```shell
curl -G 'http://localhost:8000/api/average-duration?year=2024&month=4&day=1'
```

#### Punctuality:

```shell
 curl -G 'http://localhost:8000/api/punctuality?year=2024'
```

## Phase 2

After our call, and because of limited time I decided to switch to a more hypothetical approach, describing how I might
build out the statistics system. Concentrating on the 'punctuality' side of things as a preference expressed by Dec.

Below I will detail an initial approach given the 5 sets of desired outputs (henceforth known as features).

- Total
- Average
- Punctuality
- Notice
- Distribution

Which share a common set of possible filters (note, not all are applicable to every output):

- Type of visit
- Delivery status
- Punctuality
- Visit duration

### Initial Architecture

#### API Structure

```
                                                                 
                                      API Request                
                                                                 
                                           │                     
                     ┌  Laravel App  ─ ─ ─ ┼ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ 
                                           │                    │
                     │     ┌───────────────┴──────────────┐      
                           │ Query Parameters Validation  │     │
                     │     │          Middleware          │      
                           └───────────────┬──────────────┘     │
                     │     ┌───────────────┴──────────────┐      
                           │  Authentication Middleware   │     │
                     │     └───────────────┬──────────────┘      
                           ┌───────────────┴──────────────┐     │
                     │     │   Authorisation Middleware   │      
                           └───────────────┬──────────────┘     │
                     │     ┌───────────────┴──────────────┐      
        ┌──────────────────│ Entity existence Middleware  │     │
        ▼            │     └───────────────┬──────────────┘      
┌───────────────┐                          │                    │
│               │    │                     │                     
│  DB Cache &   │                          │                    │
│   Database    │    │       ┌─────────────┴──────────┐          
│               │            │┌───────────────────────┴┐        │
└───────────────┘    │       ││┌───────────────────────┴┐        
        ▲                    │││                        │       │
        └────────────┼───────┴┤│   Feature Controller   │        
                              └┤                        │       │
                     │         └───────────┬────────────┘        
                                           │                    │
                     └ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ┼ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ 
                                           │                     
                                           │                     
                                           ▼                     
                           ┌──────────────────────────────┐      
                           │     JSON or CSV Response     │      
                           └──────────────────────────────┘      
```

#### Middleware

The authentication and authorisation middleware are as you would expect in a restful API, simply checking if a user can
access the system and what they can access within the system. In a similar the manner the "entity existence" middleware
would check if a specific a resource exists, in this scenario that could be a company,

The "Query Parameters Validation Middleware" would be similar to what was implemented in Phase 1, all be it much more
thorough. I'd anticipate having a separate validator per feature, possibly using traits to share common validation
patterns between features.

Ultimately by the time the request gets to the controller, we should have confidence in users right to access
the data and that what they're requesting is viable.

#### Controller Structure

Each controller (or method within a single controller), will be responsible for collating the data for a single feature.
However each controller, will make use of a query builder service which extends an abstract responsible for performing
common filtering, with the concrete class for each feature implementing an interface to ensure standard access.

The query builder service will return the prepped query, but not execute it. Instead, another service will be
responsible for executing the query and manipulating the data; this will allow us to more tightly control access to
the data. For example the 'total' feature will return very little data and require very little manipulation, but the
'distribution' feature may require programmatic processing of the raw data, and we might want to make use of generators
(for example) or some other process to reduce memory load/db-usage etc.

Note: I have started a simplistic implementation [here](app%2FService%2FCareVisitStatistics%2FPunctuality.php) and it
can be accessed via the [punctuality endpoint](#Punctuality).

#### Unknowns

- How well this approach scales
    - How much will it increase the load on the DB?
    - How much will it increase the load on the server?
    - How fast can will the reports generate with real data?
- How much actual data is there to process on dev?
- What is an acceptable delay to generate the statistics?
- How many of these reports will be requested at any 1 time?
- Will the raw SQL work on the production DB (sqlite vs ??)

## Useful Commands

### Migrations

```shell
php artisan migrate
```

### SeedDatabase

```shell
php artisan db:seed
```

### Rebuild DB with seed data

```shell
php artisan migrate:fresh --seed
```

### Create a controller

```shell
php artisan make:controller CareVisitController
```
