<?php

namespace DataFilter;

/**
 * Filter profiles
 */
class ProfileGroup
{

    /** @var Profile[]  */
    protected $profiles = [];
    /** @var string */
    protected $currentProfile;

    public function __construct(array $profiles)
    {
        $this->profiles = [];
        foreach ($profiles as $name => $definition) {
            $this->addProfile($name, $definition);
        }
    }

    /**
     * Add named profile
     *
     * @param string $profileName  Name of the data filter profile
     * @param mixed   $profileDefinition   Either profile definition or profile object
     */
    public function addProfile(string $profileName, $profileDefinition): void
    {
        $this->profiles[$profileName] = $profileDefinition instanceof Profile
            ? $profileDefinition
            : new Profile($profileDefinition);
    }

    /**
     * Set a current profile
     * @throws \InvalidArgumentException
     */
    public function setProfile(string $profileName): void
    {
        if (!isset($this->profiles[$profileName])) {
            throw new \InvalidArgumentException('Profile "'. $profileName. '" does not exist');
        }
        $this->currentProfile = $profileName;
    }

    /**
     * Run checks for data on last profile, return result object
     * @throws \InvalidArgumentException
     */
    public function run(array $data, ?string $profileName = null): Result
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
     * @param string|null $profileName  Optional: profile name to use
     * @throws \InvalidArgumentException
     */
    public function check(array $data, ?string $profileName = null): bool
    {
        return !$this->run($data, $profileName)->hasError();
    }

    /**
     * Returns last result of the current profile
     * @throws \InvalidArgumentException
     */
    public function getLastResult(?string $profileName = null): Result
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
