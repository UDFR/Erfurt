<?php
declare(ENCODING = 'utf-8');
namespace Erfurt\Package;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The default TYPO3 Package Manager
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope singleton
 */
class PackageManager implements \Erfurt\Package\PackageManagerInterface {

	/**
	 * Array of available packages, indexed by package key
	 * @var array
	 */
	protected $packages = array();

	/**
	 * A translation table between lower cased and upper camel cased package keys
	 * @var array
	 */
	protected $packageKeys = array();

	/**
	 * Keys of active packages - not used yet!
	 * @var array
	 */
	protected $activePackages = array();

	/**
	 * @var string
	 */
	protected $packageStatesPathAndFilename;

	/**
	 * Initializes the package manager
	 *
	 * @param \Erfurt\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function initialize(\Erfurt\Core\Bootstrap $bootstrap) {
		$this->scanAvailablePackages();
		$this->packageStatesPathAndFilename = EF_PATH_CONFIGURATION . 'PackageStates.php';

		$packageStatesConfiguration = file_exists($this->packageStatesPathAndFilename) ? include($this->packageStatesPathAndFilename) : array();

		if ($packageStatesConfiguration === array()) {
			foreach ($this->packageKeys as $packageKey) {
				$this->activatePackage($packageKey);
			}
		}

		foreach ($this->packages as $packageKey => $package) {
			if ($packageKey === 'Erfurt' || (isset($packageStatesConfiguration[$packageKey]['state']) && $packageStatesConfiguration[$packageKey]['state'] === 'active')) {
				$this->activePackages[$packageKey] = $package;
				$package->boot($bootstrap);
			}
		}
	}

	/**
	 * Returns TRUE if a package is available (the package's files exist in the packages directory)
	 * or FALSE if it's not. If a package is available it doesn't mean neccessarily that it's active!
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if the package is available, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isPackageAvailable($packageKey) {
		return (isset($this->packages[$packageKey]));
	}

	/**
	 * Returns TRUE if a package is activated or FALSE if it's not.
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if package is active, otherwise FALSE
	 * @author Thomas Hempel <thomas@typo3.org>
	 * @api
	 */
	public function isPackageActive($packageKey) {
		return (isset($this->activePackages[$packageKey]));
	}

	/**
	 * Returns a \Erfurt\Package\PackageInterface object for the specified package.
	 * A package is available, if the package directory contains valid MetaData information.
	 *
	 * @param string $packageKey
	 * @return \Erfurt\Package The requested package object
	 * @throws \Erfurt\Package\Exception\UnknownPackageException if the specified package is not known
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPackage($packageKey) {
		if (!$this->isPackageAvailable($packageKey)) throw new \Erfurt\Package\Exception\UnknownPackageException('Package "' . $packageKey . '" is not available. Please note that package keys are case sensitive.', 1166546734);
		return $this->packages[$packageKey];
	}

	/**
	 * Returns an array of \Erfurt\Package objects of all available packages.
	 * A package is available, if the package directory contains valid meta information.
	 *
	 * @return array Array of \Erfurt\Package
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getAvailablePackages() {
		return $this->packages;
	}

	/**
	 * Returns an array of \Erfurt\Package objects of all active packages.
	 * A package is active, if it is available and has been activated in the package
	 * manager settings.
	 *
	 * @return array Array of \Erfurt\Package
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getActivePackages() {
		return $this->activePackages;
	}

	/**
	 * Returns the upper camel cased version of the given package key or FALSE
	 * if no such package is available.
	 *
	 * @param string $unknownCasedPackageKey The package key to convert
	 * @return mixed The upper camel cased package key or FALSE if no such package exists
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getCaseSensitivePackageKey($unknownCasedPackageKey) {
		$lowerCasedPackageKey = strtolower($unknownCasedPackageKey);
		return (isset($this->packageKeys[$lowerCasedPackageKey])) ? $this->packageKeys[$lowerCasedPackageKey] : FALSE;
	}

	/**
	 * Check the conformance of the given package key
	 *
	 * @param string $packageKey The package key to validate
	 * @return boolean If the package key is valid, returns TRUE otherwise FALSE
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function isPackageKeyValid($packageKey) {
		return preg_match(\Erfurt\Package\Package::PATTERN_MATCH_PACKAGEKEY, $packageKey) === 1;
	}

	/**
	 * Deactivates a package if it is in the list of active packages
	 *
	 * @param string $packageKey The package to deactivate
	 * @return void
	 * @throws \Erfurt\Package\Exception\InvalidPackageStateException If the specified package is not active
	 * @author Thomas Hempel <thomas@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 * @fixme
	 */
	public function deactivatePackage($packageKey) {
		throw new \RuntimeException('Needs refactoring');
		if ($this->isPackageActive($packageKey)) {
			unset($this->activePackages[$packageKey]);

			$packageStates = array();
			foreach ($this->packageKeys as $currentPackageKey) {

			}
			file_put_contents($this->packageStatesPathAndFilename, var_export($packageStates, TRUE));
		} else {
			throw new \Erfurt\Package\Exception\InvalidPackageStateException('Package "' . $packageKey . '" is not active.', 1166543253);
		}
	}

	/**
	 * Activates a package
	 *
	 * @param string $packageKey The package to activate
	 * @return void
	 * @throws \Erfurt\Package\Exception\InvalidPackageStateException If the specified package is already active
	 * @api
	 */
	public function activatePackage($packageKey) {
		if (!$this->isPackageActive($packageKey)) {
			$package = $this->getPackage($packageKey);
			$this->activePackages[$packageKey] = $package;

			$packageStates = file_exists($this->packageStatesPathAndFilename) ? include($this->packageStatesPathAndFilename) : array();
			$packageStates[$packageKey]['state'] = 'active';

			$packageStatesCode = var_export($packageStates, TRUE);
			file_put_contents($this->packageStatesPathAndFilename, "<?php\nreturn " . $packageStatesCode . "\n ?>");
		} else {
			throw new \Erfurt\Package\Exception\InvalidPackageStateException('Package "' . $packageKey . '" is already active.', 1244620776);
		}
	}

	/**
	 * Removes a package from registry and deletes it from filesystem
	 *
	 * @param string $packageKey package to remove
	 * @return void
	 * @throws \Erfurt\Package\Exception\UnknownPackageException if the specified package is not known
	 * @author Thomas Hempel <thomas@typo3.org>
	 * @api
	 */
	public function deletePackage($packageKey) {
		if ($packageKey === 'Erfurt') throw new \Erfurt\Package\Exception\ProtectedPackageKeyException('The package "' . $packageKey . '" is protected and cannot be removed.', 1220722120);
		if (!$this->isPackageAvailable($packageKey)) throw new \Erfurt\Package\Exception\UnknownPackageException('Package "' . $packageKey . '" is not available and cannot be removed.', 1166543253);
		if ($this->isPackageActive($packageKey)) {
			$this->deactivatePackage($packageKey);
		}

		$packagePath = $this->getPackage($packageKey)->getPackagePath();
		try {
			\Erfurt\Utility\Files::removeDirectoryRecursively($packagePath);
		} catch (\Erfurt\Utility\Exception $exception) {
			throw new \Erfurt\Package\Exception('Please check file permissions. The directory "' . $packagePath . '" for package "' . $packageKey . '" could not be removed.', 1301491089, $exception);
		}

		unset($this->packages[$packageKey]);
		unset($this->packageKeys[strtolower($packageKey)]);
	}

	/**
	 * Scans all directories in the packages directories for available packages.
	 * For each package a \Erfurt\Package\ object is created and returned as
	 * an array.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function scanAvailablePackages() {
		foreach (new \DirectoryIterator(EF_PATH_PACKAGES) as $parentFileInfo) {
			$parentFilename = $parentFileInfo->getFilename();
			if ($parentFilename[0] === '.' || !$parentFileInfo->isDir()) continue;
			foreach (new \DirectoryIterator($parentFileInfo->getPathname()) as $childFileInfo) {
				$childFilename = $childFileInfo->getFilename();
				if ($childFilename[0] !== '.') {
					$packagePath = \Erfurt\Utility\Files::getUnixStylePath($childFileInfo->getPathName()) . '/';
					$packageKey = $childFilename;
					if (isset($this->packages[$packageKey])) {
						throw new \Erfurt\Package\Exception\DuplicatePackageException('Detected a duplicate package, remove either "' . $this->packages[$childFilename]->getPackagePath() . '" or "' . $packagePath . '".', 1253716811);
					}

					$packageClassPathAndFilename = $packagePath . 'Classes/Package.php';
					if (!file_exists($packageClassPathAndFilename)) {
						$shortFilename = substr($packagePath, strlen(EF_PATH_PACKAGES)) . 'Classes/Package.php';
						throw new \Erfurt\Package\Exception\CorruptPackageException(sprintf('Missing package class in package "%s". Please create a file "%s" and extend \Erfurt\Package\Package.', $packageKey, $shortFilename), 1300782486);
					}
					require_once($packageClassPathAndFilename);
					$packageClassName = sprintf('%s\Package', $packageKey, $packageKey);
					$this->packages[$packageKey] = new $packageClassName($childFilename, $packagePath);
					if (!$this->packages[$packageKey] instanceof \Erfurt\Package\PackageInterface) {
						throw new \Erfurt\Package\Exception\CorruptPackageException(sprintf('The package class %s in package "%s" does not implement \Erfurt\Package\PackageInterface.', $packageClassName, $packageKey), 1300782487);
					}
				}
			}
		}

		foreach (array_keys($this->packages) as $upperCamelCasedPackageKey) {
			$this->packageKeys[strtolower($upperCamelCasedPackageKey)] = $upperCamelCasedPackageKey;
		}
	}
}

?>