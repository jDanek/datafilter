<?php

namespace DataFilter;

/**
 * Filter profiles
 *
 * @author Ulrich Kautz <ulrich.kautz@gmail.com>
 */

class ProfileGroup
{

    /**
     * @var array
     */
    protected $profiles;

    /**
     * @var string
     */
    protected $currentProfile;

    /**
     * Constructor for DataFilter\DataFilter
     *
     * @param array  $definition  Optional definition
     */
    public function __construct(array $profiles)
    {
        $this->profiles = array();
        foreach ($profiles as $profileName => $profileDef) {
            $this->addProfile($profileName, $profileDef);
        }
    }

    /**
     * Add named profile
     *
     * @param string  $profileName  Name of the data filter profile
     * @param mixed   $profileDef   Either profile definition or profile object
     */
    public function addProfile($profileName, $profileDef)
    {
        $this->profiles[$profileName] = $profileDef instanceof Profile
            ? $profileDef
            : new Profile($profileDef);
    }

    /**
     * Set a current profile
     *
     * @param string  $profileName  Name of the data filter profile
     *
     * @throws \InvalidArgumentException
     */
    public function setProfile($profileName)
    {
        if (!isset($this->profiles[$profileName])) {
            throw new \InvalidArgumentException('Profile "'. $profileName. '" does not exist');
        }

        $this->currentProfile = $profileName;
    }

    /**
     * Run checks for data on last profile, return result object
     *
     * @param array   $data         The data to be parsed
     * @param string  $profileName  Optional: profile name to use
     *
     * @return Result
     *
     * @throws \InvalidArgumentException
     */
    public function run(array $data, $profileName = null)
    {
        if ($profileName) {
            $this->setProfile($profileName);
        }
        if (!$this->currentProfile) {
            throw new \InvalidArgumentException("No profile set. Cannot run validation.");
        }
        return $this->profiles[$this->currentProfile]->run($data);
    }

    /**
     * Runs check for data on last profile, returns bool
     *
     * @param array   $data         The data to be parsed
     * @param string  $profileName  Optional: profile name to use
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function check(array $data, $profileName = null)
    {
        return ! $this->run($data, $profileName)->hasError();
    }

    /**
     * Returns last result of the current profile
     * @param string  $arg2  Optional: profile name to use
     *
     * @return Result
     *
     * @throws \InvalidArgumentException
     */
    public function getLastResult($profileName = null)
    {
        if ($profileName) {
            $this->setProfile($profileName);
        }
        if (!$this->currentProfile) {
            throw new \InvalidArgumentException("No profile set. Cannot run validation.");
        }
        return $this->profiles[$profileName]->getLastResult();
    }

}
