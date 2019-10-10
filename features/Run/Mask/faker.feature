Feature:
  In order to comply with the General Data Protection Regulation (EU GDPR)
  As a user
  I want to obfuscate (mask) data while moving them to the target database

  Scenario: We want to mask the email column with faker strategy
    Given there is a source database
    And there is a table users with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | email | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table users contains following data:
      | id | email      | desc   |
      | 4  | ex4@tsh.io | desc 4 |
      | 3  | ex3@tsh.io | desc 3 |
      | 1  | ex1@tsh.io | desc 1 |
      | 2  | ex2@tsh.io | desc 2 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        columns:
          email: { maskStrategy: "faker", options: { method: "email" } }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    Then the table users in target database should have 4 rows
    And the table users in target database should not contain rows:
      | email      |
      | ex1@tsh.io |
      | ex2@tsh.io |
      | ex3@tsh.io |
      | ex4@tsh.io |

  Scenario: Support Faker's optional modifier
    Given there is a source database
    And there is a table users with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | email | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table users contains following data:
      | id | email      | desc   |
      | 4  | ex4@tsh.io | desc 4 |
      | 3  | ex3@tsh.io | desc 3 |
      | 1  | ex1@tsh.io | desc 1 |
      | 2  | ex2@tsh.io | desc 2 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        columns:
          email: { maskStrategy: none, options: {  } }
          desc: { maskStrategy: "faker", options: { method: "email", modifier: "optional", modifierArguments: [0, default] } }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    Then the table users in target database should have 4 rows
    And the table users in target database should contain rows:
      | email      | desc    |
      | ex1@tsh.io | default |
      | ex2@tsh.io | default |
      | ex3@tsh.io | default |
      | ex4@tsh.io | default |
