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
use \OCA\FilesVolatile\Command\Expire;
use \OCA\FilesVolatile\Command\LastSeen;

/** @var Symfony\Component\Console\Application $application */
$application->add(new Expire(
	\OC::$server->getUserManager(),
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getRootFolder()
));

$application->add(new LastSeen(
	\OC::$server->getUserManager(),
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getRootFolder()
));