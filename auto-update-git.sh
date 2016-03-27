#!/bin/bash


alcUpdateRepo() {

  # pull master (could also update submodules...)
  git pull origin master || return 1

  plugins=()
  # git status readout
  git status --porcelain wp-content/plugins/ | { 
    while read status file; do

      # crop path down to plugin name
      pluginName=${file#wp-content/plugins/}
      pluginName=${pluginName%%/*}

      # if the plugin has already been captured, skip it
      if [[ "${plugins[@]}" =~ "${pluginName}" ]]; then
        # TODO if a plugin has multiple status how do we handle that?
        continue
      fi

      # set human readable status name
      # TODO how to determine if something has been updated removed or added?
      case "$status" in
        M )
          statusName=Updated
          ;;
        A )
          statusName=Added
          ;;
        '??' )
          statusName=Added
          ;;
        D )
          statusName=Updated
          ;;
        * )
          # skip anything else
          continue
          ;;
      esac

      # add to plugins array
      plugins+=("$statusName $pluginName")
    done 

    # Now execute commit on each plugin
    for plugin in "${plugins[@]}"; do
      if [[ $1 == 'dry' ]]; then
        echo $plugin
      else
        alcUpdateRepoGitCommit $plugin
      fi
    done
  }

}

alcUpdateRepoGitCommit() {
  git add wp-content/plugins/$2 && echo [SUCCESS] git add wp-content/plugins/$2 || return 3
  git commit -m "$1 plugin $2" && echo [SUCCESS] git commit -m \"$1 plugin $2\" || return 4
  echo ---========== $1 $2 =========---
}

alcUpdateRepo $1