Feature:
  In order to comply with the General Data Protection Regulation (EU GDPR)
  As a user
  I want to obfuscate (mask) data while moving them to the target database

  Scenario: We want to mask the email column with starify
    Given there is a source database
    And there is a table products with following columns:
      | name    | type    | length | index   |
      | id      | integer |        | primary |
      | product | string  | 64     |         |
      | desc    | string  | 128    |         |
    And the table products contains following data:
      | id | product   | desc   |
      | 4  | product 4 | desc 4 |
      | 3  | product 3 | desc 3 |
      | 1  | product 1 | desc 1 |
      | 2  | product 2 | desc 2 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      products:
        columns:
          product: { maskStrategy: "starify" }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    Then the table products in target database should have 4 rows
    And the table products in target database should contain rows:
      | id | product    |
      | 1  | ********** |
      | 2  | ********** |
      | 3  | ********** |
      | 4  | ********** |
