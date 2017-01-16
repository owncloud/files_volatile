<?php
/**
 * ownCloud
 *
 * @author Jörn Friedrich Dreyer <jfd@owncloud.com>
 * @copyright (C) 2017 Jörn Friedrich Dreyer
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files_Volatile\Command;

use OC\Files\Node\Folder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\IRootFolder;
use OCP\IDBConnection;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Expire extends Command {

	/**
	 * @var IUserManager $userManager
	 */
	private $userManager;
	/**
	 * @var IDBConnection $connection
	 */
	private $connection;

	/**
	 * @var IRootFolder
	 */
	private $rootFolder;

	public function __construct(IUserManager $userManager, IDBConnection $connection, IRootFolder $rootFolder) {
		$this->userManager = $userManager;
		$this->connection = $connection;
		$this->rootFolder = $rootFolder;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('files:volatile:expire')
			->setDescription('expire files in volatile storage')
			->addArgument(
				'userid',
				InputArgument::OPTIONAL,
				'limit to the given user'
			)
			->addOption(
				'days',
				null,
				InputOption::VALUE_REQUIRED,
				'expire files older than the number of given days',
				30
			)
			->addOption(
				'dry-run',
				null,
				InputOption::VALUE_NONE,
				'Do everything except actually deleting files.'
			);
	}


	public function execute(InputInterface $input, OutputInterface $output) {

		$userIds = $input->getArgument('userid');
		if (isset($userIds) && !is_array($userIds)) {
			$userIds = [$userIds];
		}

		$days = $input->getOption('days');
		$dryRun = $input->getOption('dry-run');
		if (empty($userIds)) {
			$userIds = $this->getSeenUsers();
		}
		foreach ($userIds as $userId) {
			$this->expire($userId, $days, $dryRun, $output);
		}
	}

	public function getSeenUsers () {
		$sql  = 'SELECT `userid` FROM `*PREFIX*preferences` ' .
			'WHERE `appid` = ? AND `configkey` = ? ';

		$result = $this->connection->executeQuery($sql, array('login', 'lastLogin'));

		while ($row = $result->fetch()) {
			yield $row['userid'];
		}
	}

	public function expire($userId, $days = 30, $dryRun, OutputInterface $output) {
		$output->writeln("Expiring volatile files for $userId");
		$folderName = \OC::$server->getConfig()->getAppValue('files_volatile', 'folder-name', 'Volatile Files');

		\OC_Util::tearDownFS();
		\OC_Util::setupFS($userId);

		try {
			$homeFolder = \OC::$server->getUserFolder($userId);
			if (!$homeFolder) {
				$output->writeln("<error>No home folder for $userId</error>");
				return;
			}
			$volatileFolder = $homeFolder->get($folderName);
			if (!$volatileFolder instanceof Folder) {
				$output->writeln("<error>No volatile folder for $userId</error>");
				return;
			}
		} catch (NotFoundException $ex) {
			$output->writeln("<error>NotFoundException {$ex->getMessage()}</error>");
			return;
		}
		foreach ($volatileFolder->getDirectoryListing() as $node) {
			$this->expireNode($node, $days, $dryRun, $output);
		};
	}
	public function expireNode(Node $node, $days = 30, $dryRun = false, OutputInterface $output) {
		//we propagate mtimes so we can delete a dir if it hasn't been changed
		if ($this->isOlderThan($node->getMTime(), $days)) {
			$output->writeln("deleting {$node->getPath()}");
			if ($dryRun === false) {
				$node->delete();
			}
		} else if ($node instanceof Folder) {
			$this->expireNode($node, $days, $output);
		}
	}

	public function isOlderThan($mtime, $days = 30) {
		$date = new \DateTime($days . ' days ago');
		return $mtime < $date->getTimestamp();
	}
}