#!/usr/bin/env bash
# Run on your LAPTOP: builds a numbered version of the web image and pushes it.
# First time: docker login
#
# Usage:  ./release.sh v1
#         ./release.sh v2      (next release, and so on)
#
# Each version stays on Docker Hub forever, so EC2 can roll back to any of them.
set -euo pipefail
cd "$(dirname "$0")"

# ====== EDIT THIS (must match image: in deploy/compose.yml) ======
IMAGE_REPO="jiale291/ict2212_student17-web"
# ================================================================

VERSION="${1:-}"
IN_GIT=false
git rev-parse --is-inside-work-tree >/dev/null 2>&1 && IN_GIT=true

if [[ -z "$VERSION" ]]; then
  echo "Usage: ./release.sh <version>   e.g. ./release.sh v1"
  if $IN_GIT; then
    LAST=$(git tag --list 'v*' --sort=-v:refname | head -n1)
    echo "Last release: ${LAST:-none yet}"
  fi
  exit 1
fi

if [[ ! "$VERSION" =~ ^[A-Za-z0-9._-]+$ || "$VERSION" == "latest" ]]; then
  echo "ERROR: version must be letters/numbers/dots/dashes (e.g. v1, v1.2), and not 'latest'"
  exit 1
fi

# Never overwrite an existing version, otherwise rollback would be pointless
if docker manifest inspect "$IMAGE_REPO:$VERSION" >/dev/null 2>&1; then
  echo "ERROR: $IMAGE_REPO:$VERSION already exists on Docker Hub. Pick a new version."
  exit 1
fi

GIT_COMMIT="none"
if $IN_GIT; then
  GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo none)
  if git rev-parse "$VERSION" >/dev/null 2>&1; then
    echo "ERROR: git tag $VERSION already exists. Pick a new version."
    exit 1
  fi
  if [[ -n "$(git status --porcelain)" ]]; then
    echo "WARNING: you have uncommitted changes. They WILL be in the image,"
    echo "but not in git, so nobody can see what $VERSION contains."
    read -r -p "Continue anyway? (y/n) " ans
    [[ "$ans" == "y" ]] || exit 1
  fi
fi

echo "==> Building $IMAGE_REPO:$VERSION (linux/amd64, commit $GIT_COMMIT)"
docker build --platform linux/amd64 \
  -f docker/web/Dockerfile \
  --label "app.version=$VERSION" \
  --label "app.git-commit=$GIT_COMMIT" \
  -t "$IMAGE_REPO:$VERSION" \
  -t "$IMAGE_REPO:latest" \
  .

echo "==> Pushing $VERSION and latest"
docker push "$IMAGE_REPO:$VERSION"
docker push "$IMAGE_REPO:latest"

if $IN_GIT; then
  echo "==> Tagging git commit as $VERSION"
  git tag "$VERSION"
  git push origin "$VERSION" 2>/dev/null || echo "    (could not push git tag, run: git push origin $VERSION)"
fi

echo
echo "Released $VERSION. On EC2 run:  sudo bash ~/deploy/update.sh $VERSION"
