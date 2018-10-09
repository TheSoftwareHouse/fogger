Feature:
  In order to subset the table that has foreign key relations
  As a user
  I want to get obfuscated, subsetted but still consistent database.

  Scenario: We want to refine table that references itself - column is nullable
    Given there is a source database
    And there is a table users with following columns:
      | name       | type    | length | index   | nullable |
      | id         | integer |        | primary |          |
      | email      | string  | 64     | unique  |          |
      | supervisor | string  | 64     |         | true     |
    And the table users contains following data:
      | id | email      | supervisor |
      | 1  | ex1@tsh.io | ex2@tsh.io |
      | 2  | ex2@tsh.io | ex3@tsh.io |
      | 3  | ex3@tsh.io | ex2@tsh.io |
      | 4  | ex4@tsh.io | ex1@tsh.io |
    And the users.supervisor references users.email
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        subsetStrategy: tail
        subsetOptions: { length: 3 }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    And I run "finish" command with input:
      | --file | test.yaml |
    Then the command should exit with code 0
    And the table users in target database should have 3 rows
    And the table users in target database should contain rows:
      | id | email      | supervisor |
      | 2  | ex2@tsh.io | ex3@tsh.io |
      | 3  | ex3@tsh.io | ex2@tsh.io |
      | 4  | ex4@tsh.io |            |
