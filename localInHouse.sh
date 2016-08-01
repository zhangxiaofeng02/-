xcodebuild clean

xcodebuild -workspace 项目名称.xcworkspace -scheme  项目名称 -configuration InHouse -sdk iphoneos build CODE_SIGN_IDENTITY="证书名"

xcrun -sdk iphoneos PackageApplication -v $PWD/Build/Products/InHouse-iphoneos/项目名称.app

php $PWD/buildHandle.php $1 $2 $3 $4 $5

tar zcvf $PWD/Build/Products/InHouse-iphoneos/项目名称.app.dSYM.tar.gz $PWD/Build/Products/InHouse-iphoneos/项目名称.app.dSYM

open $PWD

