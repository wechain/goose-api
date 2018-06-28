#!/bin/bash


# set port
[[ -z "$2" ]] && port=8000 || port=$2


# func / start server
start() {
	php -S localhost:$port -t ./
}

# func / install
install() {
	php resource/install.php
}


case "$1" in
	start)
		start
		;;

	install)
		install
		;;

	*)
		echo "Usage: ./dev-server.sh {start}" >&2
		exit 3
		;;
esac