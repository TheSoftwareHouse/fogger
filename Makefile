DOCKER_COMPOSE = docker-compose -f docker-compose.yml -f docker-compose.override.yml
DOCKER_COMPOSE_TEST_MYSQL = ${DOCKER_COMPOSE} -f docker-compose.test-mysql.yml
DOCKER_COMPOSE_TEST_POSTGRES = ${DOCKER_COMPOSE} -f docker-compose.test-postgres.yml

# --- docker
.PHONY: pull
pull:
	${DOCKER_COMPOSE} pull

.PHONY: install
install:
	${DOCKER_COMPOSE} run --rm --entrypoint="composer" app install

.PHONY: start
start:
	${DOCKER_COMPOSE} up -d
	echo "waiting for services to start..."
	sleep 30

.PHONY: stop
stop:
	${DOCKER_COMPOSE} stop

# --- test
.PHONY: test
test:
	make test-mysql
	make test-postgres

.PHONY: test-mysql
test-mysql:
	${DOCKER_COMPOSE_TEST_MYSQL} up -d --scale=worker=0
	sleep 16
	${DOCKER_COMPOSE_TEST_MYSQL} run --rm --entrypoint="php" app vendor/bin/behat --format=progress
	${DOCKER_COMPOSE_TEST_MYSQL} run --rm --entrypoint="php" app vendor/bin/phpspec run
	${DOCKER_COMPOSE} up -d --remove-orphans

.PHONY: test-postgres
test-postgres:
	${DOCKER_COMPOSE_TEST_POSTGRES} up -d --scale=worker=0
	sleep 16
	${DOCKER_COMPOSE_TEST_POSTGRES} run --rm --entrypoint="php" app vendor/bin/behat --format=progress
	${DOCKER_COMPOSE_TEST_POSTGRES} run --rm --entrypoint="php" app vendor/bin/phpspec run
	${DOCKER_COMPOSE} up -d --remove-orphans

# --- fogger
.PHONY: init
init:
	${DOCKER_COMPOSE} run --rm app fogger:init

.PHONY: run
run:
	${DOCKER_COMPOSE} run --rm app fogger:run

.PHONY: finish
finish:
	${DOCKER_COMPOSE} run --rm app fogger:finish
