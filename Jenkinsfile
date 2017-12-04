node {
    checkout scm
    stage('Build') {
        sh 'bash bin/composer.sh'
        sh 'php composer.phar install --prefer-dist --no-progress --no-suggest --no-ansi'
    }
    stage('Lint') {
        sh 'find . -name "*.php"  -not -path "./vendor/*" -print0 | xargs -l1 -0 php -l'
        sh 'find . -name "*.sh" -not -path "./vendor/*" -print0 | xargs -l1 -0 shellcheck -s bash'
    }
    stage('Test') {
        sh 'php vendor/bin/phpunit --disallow-test-output'
    }
}
