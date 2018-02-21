# Woofr
Mail delivery safety as an API

```
RUN service jenkins start
cat /var/lib/jenkins/secrets/initialAdminPassword

node {
   stage("post") {
       git "https://github.com/matula/woofr.git"
       sh 'composer install'
       sh "npm install"
   }
}

```

