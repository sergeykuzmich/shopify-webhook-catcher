default: run

build:
	docker-compose build

run:
	docker-compose up

clean:
	docker-compose down --volumes

clean_run: clean build run

qa: clean build
	docker-compose up application && docker-compose down --volumes
