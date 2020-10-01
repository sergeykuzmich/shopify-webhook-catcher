default: run

clean:
	docker-compose down --volumes

run: clean
	sed -i '' 's/target: qa/target: application/g' docker-compose.yml
	docker-compose build
	docker-compose up

qa: clean
	sed -i '' 's/target: application/target: qa/g' docker-compose.yml
	docker-compose build
	docker-compose up application
	docker-compose down --volumes
