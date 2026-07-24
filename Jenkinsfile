pipeline {
    agent any

    environment {
        NET_NAME   = "alis-net-${BUILD_NUMBER}"
        MYSQL_NAME = "alis-mysql-${BUILD_NUMBER}"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('PHP Syntax Lint') {
            steps {
                sh '''
                    docker run --rm -v "$WORKSPACE":/app -w /app php:8.2-cli \
                        bash -c "find . -name '*.php' -not -path './vendor/*' | xargs -I{} php -l {}"
                '''
            }
        }

        stage('Levantar MySQL') {
            steps {
                sh '''
                    docker network create ${NET_NAME}

                    docker run -d --name ${MYSQL_NAME} --network ${NET_NAME} \
                        -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
                        -e MYSQL_DATABASE=libreria_alis \
                        mysql:8.0

                    echo "Esperando a que MySQL esté listo..."
                    for i in $(seq 1 30); do
                        docker exec ${MYSQL_NAME} mysqladmin ping -h 127.0.0.1 --silent && break
                        sleep 2
                    done
                '''
            }
        }

        stage('Importar schema') {
            steps {
                sh '''
                    docker run --rm --network ${NET_NAME} -v "$WORKSPACE":/app -w /app mysql:8.0 \
                        bash -c "mysql -h ${MYSQL_NAME} -u root libreria_alis < sql/schema.sql"
                '''
            }
        }

        stage('Instalar dependencias Composer') {
            steps {
                sh '''
                    docker run --rm -v "$WORKSPACE":/app -w /app composer:2 \
                        composer require --dev phpunit/phpunit:^10 --no-interaction --quiet
                '''
            }
        }

        stage('Ajustar config DB para CI') {
            steps {
                sh '''
                    docker run --rm -v "$WORKSPACE":/app -w /app php:8.2-cli \
                        sed -i "s/define('DB_PASS', '.*')/define('DB_PASS', '')/" config/database.php
                '''
            }
        }

        stage('Tests unitarios') {
            steps {
                sh '''
                    docker run --rm --network ${NET_NAME} -v "$WORKSPACE":/app -w /app \
                        -e MYSQLHOST=${MYSQL_NAME} php:8.2-cli \
                        bash -c "docker-php-ext-install pdo pdo_mysql mbstring >/dev/null 2>&1; vendor/bin/phpunit tests/LangTest.php tests/AuthTest.php --colors=never"
                '''
            }
        }

        stage('Tests de integración') {
            steps {
                sh '''
                    docker run --rm --network ${NET_NAME} -v "$WORKSPACE":/app -w /app \
                        -e MYSQLHOST=${MYSQL_NAME} php:8.2-cli \
                        bash -c "docker-php-ext-install pdo pdo_mysql mbstring >/dev/null 2>&1; vendor/bin/phpunit tests/LibrosIntegrationTest.php --colors=never"
                '''
            }
        }

        stage('Chequeo de headers de seguridad') {
            steps {
                sh '''
                    for f in api/libros.php api/search.php api/reservas.php api/google_books.php; do
                        if grep -q "ob_start" "$f"; then
                            echo "OK: $f tiene ob_start()"
                        else
                            echo "FALTA: $f no tiene ob_start()" && exit 1
                        fi
                    done

                    for f in api/*.php; do
                        if grep -q "display_errors.*1" "$f"; then
                            echo "FALLA: $f tiene display_errors activo" && exit 1
                        fi
                    done
                    echo "OK: display_errors desactivado en todos los endpoints"
                '''
            }
        }

        stage('Build imagen Docker') {
            steps {
                sh 'docker build -t libreria-alis:${BUILD_NUMBER} .'
            }
        }
    }

    post {
        always {
            sh '''
                docker rm -f ${MYSQL_NAME} || true
                docker network rm ${NET_NAME} || true
            '''
        }
    }
}
