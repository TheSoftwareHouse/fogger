Feature:
  In order to use the fogger tool
  As a user
  I want to read database schema and create boilerplate config file

  Scenario: It creates the boilerplate by running init command
    And there is a source database
    And there is a table table with following columns:
      | name   | type    | length | comment                             |
      | id     | integer |        |                                     |
      | column | string  | 64     | fogger::strategy                    |
      | other  | string  | 128    | fogger::strategy{"option": "value"} |
    And the file test.yaml doesn't exist
    When I run "init" command with input:
      | --file | test.yaml |
    Then I should see "Done!" in command's output
    And the command should exit with code 0
    And YAML file test.yaml should be like:
    """
    tables:
      table:
        columns:
          id: { maskStrategy: none, options: {  } }
          column: { maskStrategy: strategy, options: {  } }
          other: { maskStrategy: strategy, options: { option: "value" } }
        subsetStrategy: null
        subsetOptions: {  }
    excludes: {  }

    """
