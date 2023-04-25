# Copyright (c) 2018 Sebastian Kucharczyk <kuchen@kekse.biz>
# 
# Copy this file to '/etc/profile.d/up2date.sh'!
#
# You just need to call `up2date`, rest is automatic (including OS detection).
# Supports Gentoo, Debian and Termux Linux (my all-time favorites ;-)
# 
# If `source`d as non-root, this file will be ignored (except on Termux).

# functions only for 'root'!
if [ `id -u` -ne 0 ]; then
	if [ ! -d "/data/data/com.termux/" ]; then
		return 1
	fi
fi

os=""

OS()
{
	_gentoo="`which emerge 2>/dev/null`"
	_debian="`which apt 2>/dev/null`"
	_termux="`which pkg 2>/dev/null`"

	if [ -z "$_gentoo" -a -z "$_debian" -a -z "$_termux" ]; then
		echo "You neither use 'Gentoo' Linux nor 'Debian' Linux!" >&2
		return 1
	fi

	if [ -n "$_termux" ]; then
		os="termux"
	elif [ -n "$_gentoo" ]; then
		os="gentoo"
	elif [ -n "$_debian" ]; then
		os="debian"
	else
		os="unknown"
	fi

	if [ -d "/data/data/com.termux/" -a -z "$_termux" ]; then
		echo "Obviously you're using Termux, but it's not selected (\`pkg\` missing?)" >&2
		return 2
	fi

	export os
}

OS

up2date()
{
	case "$os" in
		gentoo)
			gentoo;;
		debian)
			debian;;
		termux)
			termux;;
		*)
			echo "Unknown Linux distribution!" >&2
			return 1
			;;
	esac

	echo
	echo -e "\n\nNow just call \`cfg\` to find updated configuration files.."
}

up2time()
{
	[ -n "$NO_NTP" ] && return

	SERVER="de.pool.ntp.org"

	_ntpdate="`which ntpdate 2>/dev/null`"
	_hwclock="`which hwclock 2>/dev/null`"

	if [ -z "$_ntpdate" ]; then
		return 255
	fi

	$_ntpdate $SERVER || return 1

	if [ -n "$_hwclock" ]; then
		hwclock --systohc || return 2
	fi
}

up2file()
{
	[ -n "$NO_UPDATEDB" ] && return

	_updatedb="`which updatedb 2>/dev/null`"

	if [ -z "$_updatedb" ]; then
		return 255
	fi

	$_updatedb || return 1
}

up2npm()
{
	[ -n "$NO_NPM" ] && return

	_npm="`which npm 2>/dev/null`"

	if [ -z "$_npm" ]; then
		return 255
	fi

	$_npm install -g npm@latest || return 1
}

termux()
{
	up2time

	_pkg="`which pkg 2>/dev/null`"
	_apt="`which apt 2>/dev/null`"
	_apt_file="`which apt-file 2>/dev/null`"

	if [ -z "$_pkg" ]; then
		if [ -z "$_apt" ]; then
			echo "Neither 'pkg' nor 'apt' found!" >&2
			return 255
		fi
	else
		$_pkg upgrade
	fi

	if [ -z "$NO_SYNC" ]; then
		if [ -n "$_apt" ]; then
			$_apt update || return 1
		fi

		if [ -n "$_apt_file" ]; then
			$_apt_file update || return 2
		fi
	fi

	if [ -n "$_pkg" ]; then
		$_pkg upgrade
	fi

	if [ -n "$_apt" ]; then
		$_apt full-upgrade || return 3

		$_apt autoremove || return 4
		$_apt autoclean || return 5
	fi

	up2npm
	up2file
}

debian()
{
	up2time

	_apt="`which apt 2>/dev/null`"
	_apt_file="`which apt-file 2>/dev/null`"

	if [ -z "$_apt" ]; then
		echo "'apt' is not installed!" >&2
		return 255
	fi

	if [ -z "$NO_SYNC" ]; then
		$_apt update || return 1

		if [ -n "$_apt_file" ]; then
			$_apt_file update || return 2
		fi
	fi

	$_apt full-upgrade || return 3

	$_apt autoremove || return 4
	$_apt autoclean || return 5

	up2npm
	up2file
}

gentoo()
{
	up2time

	_emerge="`which emerge 2>/dev/null`"

	if [ -z "$_emerge" ]; then
		echo "'emerge' is not installed!" >&2
		return 255
	fi

	[ -z "$NO_SYNC" ] && ( $_emerge --sync || return 1 )

	$_emerge --ask --verbose --update --deep --newuse --with-bdeps=y --backtrack=32 @system @world || return 2

	$_emerge --ask --verbose @preserved-rebuild || return 3

        $_emerge --ask --verbose @module-rebuild || return 4

	[ -n "`which revdep-rebuild 2>/dev/null`" ] && ( revdep-rebuild || return 5 )

	[ -n "$WITH_MODULES" ] && ( $_emerge --ask --verbose @module-rebuild || return 6 )

	$_emerge --ask --depclean || return 7

	[ -z "$NO_CLEAN_DISTFILES" ] && rm -rf /usr/portage/distfiles/* 2>/dev/null

	up2npm
	up2file
}

cfg()
{
	gentooCfg()
	{
		find /etc/ -iname '._cfg*'
	}

	debianCfg()
	{
		find /etc/ -iname '*.dpkg*'
	}

	termuxCfg()
	{
		find /data/data/com.termux/files/usr/etc/ -iname '*.dpkg'
	}

	case "$os" in
		gentoo)
			gentooCfg;;
		debian)
			debianCfg;;
		termux)
			termuxCfg;;
		*)
			echo "Unknown Linux distribution!" >&2
			return 1
			;;
	esac
}
