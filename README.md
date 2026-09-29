# Record The BCH Blockchain

- This bot was created to fullfil bounty on gitcoin [Gitcoin bounty](https://github.com/ArweaveTeam/Bounties/issues/23)

## Requirements

* Ubuntu 18.04 minimum
* PHP 

## Install

- Install required php packages
```
sudo apt install php-cli php-common php-curl php-zip php-bcmath php-bz2 php-gmp php-mbstring php-xml git curl
```

- Clone from github
```
git clone https://github.com/rickrods/arweave-record-the-bch-blockchain.git

cd arweave-record-the-bch-blockchain/
```

- Copy your  wallet file to jwk.json
``` 
cp <wallet_source_location> jwk.json
```

## Use

- Set the block height to start at it MUST be a number the example below starts at block 10
```
echo 10 > blocks.txt
```

- Add the script to cron the example below is every 10 minutes **source** [Crontab Guru](https://crontab.guru/every-10-minutes)
```
crontab -e
```
```
*/10 * * * * php -f blocks.php >> bchchain.log
```
# Data Location

All data is saved here 

https://viewblock.io/arweave/address/jN91f8NkkiP4X9isB00wkri_-M86Iv-1oK6gLyC4EVk


## Update 9-29-26
The original library arweave-php is unmaintained.

I thought I would attempt to modernize the arweave-php library and see if I could resolve the security issues that have developed over the years. 

I forked and modernized the arweave-php library [modernized version now located here](https://github.com/rickrods/arweave-php/tree/modernization) 

I have not tested the updates as of today perhaps someday soon...