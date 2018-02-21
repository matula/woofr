FROM perkdotcom/application:7.1.42
RUN yum -y update
# Adding vim and zsh
RUN yum -y install vim
RUN yum -y install zsh
RUN wget https://github.com/robbyrussell/oh-my-zsh/raw/master/tools/install.sh -O - | zsh || true
RUN wget -O /etc/yum.repos.d/jenkins.repo https://pkg.jenkins.io/redhat-stable/jenkins.repo
RUN rpm --import https://pkg.jenkins.io/redhat-stable/jenkins.io.key
RUN yum -y install java-1.8.0-openjdk
RUN yum -y install jenkins
# RUN service jenkins start
# cat /var/lib/jenkins/secrets/initialAdminPassword

RUN rm /etc/nginx/conf.d/default.conf
# some custom aliases I like
RUN echo 'alias a="php artisan"' >> ~/.zshrc
RUN echo 'alias coda="composer dump-autoload"' >> ~/.zshrc
RUN echo 'alias pu="vendor/bin/phpunit"' >> ~/.zshrc
COPY .docker/config/nginx.conf /etc/nginx/nginx.conf
COPY .docker/config/supervisord.conf /etc/supervisord.conf
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
