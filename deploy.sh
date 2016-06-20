#!/bin/sh
#
fatal() {
  echo "$@" 1>&2
  exit 1
}

set -e
echo "DIY DEPLOY OPENSHIFT"
[ -n "$OPENSHIFT_SECRET" ] && echo "SECRET $(expr length "$OPENSHIFT_SECRET")"
[ -n "$TRAVIS_BRANCH" ] && echo "Branch: $TRAVIS_BRANCH"
[ -n "$TRAVIS_PULL_REQUEST" ] && echo "PR: $TRAVIS_PULL_REQUEST"
[ -n "$TRAVIS_REPO_SLUG" ] && echo "Slug: $TRAVIS_REPO_SLUG"
[ -n "$TRAVIS_TAG" ] && echo "Tag: $TRAVIS_TAG"
if [ -n "$TRAVIS_BRANCH" ] ; then
  OPENSHIFT_APP="$(grep ' - deploy '"$TRAVIS_BRANCH"':' .travis.yml | cut -d: -f2)"
  [ -n "$OPENSHIFT_APP" ] && echo "App: $OPENSHIFT_APP"
fi
for k in OPENSHIFT_USER OPENSHIFT_SECRET OPENSHIFT_APP
do
  eval v="\$$k"
  [ -z "$v" ] && fatal "MISSING $k"
done
#
gem install rhc
AUTH="-l $OPENSHIFT_USER -p $OPENSHIFT_SECRET"
rhc app-show $OPENSHIFT_APP $AUTH
GITURL="$(rhc app-show $OPENSHIFT_APP $AUTH| grep '  Git URL: ' | cut -d: -f2-)"
[ -z "$GITURL" ] && fatal "MISSING GITURL"
GITHOST="$(echo $GITURL | cut -d'@' -f2 | cut -d/ -f1)"
[ -z "$GITHOST" ] && fatal "MISSING GITHOST"
ssh-keyscan $GITHOST > ~/.ssh/known_hosts

yes '' | ssh-keygen -N ''
rhc sshkey remove temp $AUTH || true
rhc sshkey add temp $HOME/.ssh/id_rsa.pub $AUTH
git remote add openshift -f $GITURL
git merge openshift/master -s recursive -X ours
git push openshift HEAD:master
