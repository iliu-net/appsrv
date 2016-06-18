#!/bin/sh
#
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
  [ -z "$v" ] && exit
done
#
gem install rhc
AUTH="-l $OPENSHIFT_USER -p $OPENSHIFT_SECRET"
GITURL="$(rhc app-show wpdev $AUTH| grep '  Git URL: ' | cut -d: -f2-)"
[ -z "$GITURL" ] && exit
GITHOST="$(echo $GITURL | cut -d'@' -f2 | cut -d/ -f1)"
[ -z "$GITHOST" ] && exit
ssh-keyscan $GITHOST > ~/.ssh/known_hosts

yes '' | ssh-keygen -N ''
rhc sshkey remove temp $AUTH || true
rhc sshkey add temp $HOME/.ssh/id_rsa.pub $AUTH
git remote add openshift -f $GITURL
git merge openshift/master -s recursive -X ours
git push openshift HEAD:master
