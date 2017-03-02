#!/usr/bin/env groovy

node('master') {
    stage('build') {
        git url: 'git@github.com:codebasement/ticketbeast.git'

        // Start services (Let docker-compose build containers for testing)
        sh "./dev up -d"

        // Get composer dependencies
        sh "./dev composer install"

        // Create .env file for testing
        sh 'cp .env.example .env'
        sh './dev art key:generate'
        sh 'sed -i "s/REDIS_HOST=.*/REDIS_HOST=redis/" .env'
        sh 'sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env'
        sh 'sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=redis/" .env'
    }
    stage('test') {
        sh "APP_ENV=testing ./dev test"
    }
}