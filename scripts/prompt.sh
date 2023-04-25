#
# Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
#
# Just copy this script to '/etc/profile.d/'.
#

#
_SLASHES=4
_REST_STRING="..."
_WITH_DATE=0
_DATE_FORMAT='%H:%M:%S (%j)'
_WITH_HOSTNAME=1
_WITH_USERNAME=1

#
ps1Prompt()
{
	#
	ret=$?

	#
	startFG()
	{
		PS1="$PS1"'\[\033[38;2;'"$1;$2;$3"'m\]'
	}

	startBG()
	{
		PS1="$PS1"'\[\033[48;2;'"$1;$2;$3"'m\]'
	}

	startBold()
	{
		PS1="$PS1"'\[\033[1m\]'
	}

	ansiReset()
	{
		PS1="$PS1"'\[\033[m\]'
	}

	write()
	{
		PS1="$PS1$*"
	}

	getBase()
	{
		_depth=$1
		shift
		_dir="$*"
		res=""
		slashCount=0

		if [[ ${_dir} == "/" ]]; then
			write ' / '
			return
		fi

		while [[ "${_dir: -1}" = "/" ]]; do
			_dir="${_dir::-1}"
		done

		homeLen=${#HOME}
		
		if [[ "$_dir" == "$HOME" ]]; then
			_dir="~"
		elif [[ "${_dir:0:$(($homeLen + 1))}" = "$HOME/" ]]; then
			_dir="~${_dir:$homeLen}"
		fi

		for (( i=${#_dir}-1; i >= 0; i-- )); do
			if [[ ${_dir:$i:1} == "/" ]]; then
				let slashCount=$slashCount+1
				res="/${res}"

				if [[ $slashCount -eq $_depth ]]; then
					inHome=0
					upper=""

					for (( j=$i-1; j >= 0; j--)); do
						if [[ "${_dir:$j:1}" != "/" ]]; then
							upper="${_dir:$j:1}${upper}"
						fi
						
						if [[ "$upper" == "~" ]]; then
							inHome=1
							break
						fi
					done

					if [[ $inHome -ne 0 ]]; then
						res="~${res}"
					elif [[ $i -gt 0 ]]; then
						res="${_REST_STRING}${res}"
					fi
					break
				fi
			else
				res="${_dir:$i:1}${res}"
			fi
		done

		write " $res "
	}

	#
	PS1=""

	#
	write ' ➜ '
	user_host=0

	#
	if [[ $_WITH_USERNAME -ne 0 ]]; then
		if [[ `id -u` -eq 0 ]]; then
			startBG 175 65 245
		elif [[ `id -g` -eq 0 ]]; then
			startFG 175 65 245
		else
			startFG 225 245 70
		fi

		startBold
		write "`id -nu`"
		ansiReset
		user_host=1
	fi

	if [[ $_WITH_HOSTNAME -ne 0 ]]; then
		write '@'
		startFG 245 195 65
		write "$HOSTNAME"
		ansiReset
		user_host=1
	fi

	[[ $user_host -ne 0 ]] && write ' '
	
	#
	if [[ $_WITH_DATE -ne 0 && -n "$_DATE_FORMAT" ]]; then
		write "`date +"$_DATE_FORMAT"` "
	fi

	#
	if [[ $ret -eq 0 ]]; then
		startBG 170 230 70
		startFG 0 0 0
		write ' ✔ '
		ansiReset
	else
		startBG 210 45 25
		startFG 255 255 255
		write ' ✘ '
		ansiReset
	fi

	#
	jc=`jobs -p | wc -l`
	if [[ $jc -gt 0 ]]; then
		write ' '
		startBG 140 30 140
		startFG 255 255 255
		write " $jc "
		ansiReset
	fi
	
	#
	write ' '
	startBG 95 160 205
	startFG 0 0 0
	getBase $_SLASHES "$PWD"
	ansiReset
	write ' '

	#
	export PS1
}

export PS1=''
export PROMPT_COMMAND=ps1Prompt

