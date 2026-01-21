License: Proprietary – commercial use requires prior authorization.

# install docker in wsl2 and run sso

_approved env is W11 with WSL2_

## update apt
```shell
sudo apt update
```

## install docker
```shell
sudo apt install -y docker.io docker-compose
```

## start docker
```shell
sudo service docker start
```

## check docker version
### docker engine
```shell
docker --version
```
### docker manager for .yml
```shell
docker-compose --version
```

## add current user to docker users
```shell
sudo usermod -aG docker $USER
```
_you have to restart VSCode/WSL (**shutdown**) after that_

## check groups
```shell
groups
```
_should display **docker**_

## install make
```shell
sudo apt install -y make
```

## start docker project
```shell
make
make rebuild
```

## open your project
[sso app](http://localhost:8084/sso/)
[phpmyadmin](http://localhost:8085/)

## login
admin
pwd
_you will have to change your password immediately_
⚠️ These credentials are intentionally hardcoded for demo purposes.

## config
surely you'll want to check src/sso/env.php


## examples
Examples were mostly redacted.
They are not supposed to work outside of corporate context - host port does not match docker's one, for example.
Their purpose is only to display an implementation for legacy code, and some little joys in the process.
