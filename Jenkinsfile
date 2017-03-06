#!/usr/bin/env groovy

node('master') {
    stage('build') {
        git url: 'git@github.com:codebasement/ticketbeast.git'

        // Start services (Let docker-compose build containers for testing)
        sh "./dev up -d"

        // Get composer dependencies
        sh "./dev composer install"

        // Create .env file for testing
        sh '/var/lib/jenkins/.venv/bin/aws s3 cp s3://ticketbeast-secrets/env-ci .env'
        sh './dev art key:generate'

    stage('test') {
        sh "APP_ENV=testing ./dev test"
    }
}