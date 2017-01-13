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

namespace OCA\FilesVolatile;


class Manager {

	public function addVolatileFolder($params) {
		// TODO implement a storage that represents a link to a different portion
		// of the virtual filetree to make the app compatible with / use an objectstore
		if (empty($params['user'])) {
			return;
		}
		$dataDir = \OC::$server->getUserManager()->get($params['user'])->getHome();
		$dataDir .= '/files_volatile';
		if (!is_dir($dataDir)) {
			mkdir($dataDir, 770, true);
		}
		$folderName = \OC::$server->getConfig()->getAppValue(
			'files_volatile', 'folder-name', 'Volatile Files'
		);
		\OC\Files\Filesystem::mount(
			'\OC\Files\Storage\Local',
			['datadir' => $dataDir],
			"/{$params['user']}/files/$folderName"
		);

	}

}