<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Exception;

/**
 * @ORM\Entity
 */
class SearchIntent
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $searchType;

    /**
     * @ORM\Column(type="array")
     */
    private array $conditions;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="searchIntents", cascade={"persist"})
     */
    private Project $project;

    /**
     * @return string
     */
    public function getSearchType(): string
    {
        return $this->searchType;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param object $query JSON search query decoded in array format
     * @throws Exception Stringify error in query
     */
    public function setSearch(object $query): void
    {
        try {
            $this->isValidQueryFormat($query);
        } catch (Exception $e) {
            throw new Exception('Bad format for search format, ' . strtolower($e->getMessage()));
        }

        $this->searchType = $query->type;
        $this->conditions = $query->conditions;
    }

    /**
     * @param object $query JSON search query decoded in array format
     * @return bool true if query format is valid
     * @throws Exception the error in the query
     */
    private function isValidQueryFormat(object $query): bool
    {
        $requiredQueryProperties = ['type', 'conditions'];

        // Check if required search properties exist in $query
        foreach ($requiredQueryProperties as $requiredQueryProperty) {
            if (!property_exists($query, $requiredQueryProperty)) {
                throw new Exception(sprintf('Missing property "%s"', $requiredQueryProperty));
            }
        }

        // Check if $query has only accepted properties
        $queryPropertiesArray = get_object_vars($query);
        foreach ($queryPropertiesArray as $queryProperty => $queryPropertyValue) {
            if (!in_array($queryProperty, $requiredQueryProperties, true)) {
                throw new Exception(sprintf('Unknown property "%s", only [%s] are accepted', $queryProperty, implode(', ', $requiredQueryProperties)));
            }
        }


        // Check all conditions properties
        $conditions = $query->conditions;
        $requiredConditionProperties = ['property', 'rule', 'value'];

        foreach ($conditions as $condition) {

            // Check if required condition properties exist in all conditions
            foreach ($requiredConditionProperties as $validConditionProperty) {
                if (!property_exists($condition, $validConditionProperty)) {
                    throw new Exception(sprintf('Missing property "%s" in conditions', $validConditionProperty));
                }
            }

            // Check if all conditions has only accepted properties
            $conditionPropertiesArray = get_object_vars($condition);

            foreach ($conditionPropertiesArray as $conditionPropertyName => $conditionValue) {
                if (!in_array($conditionPropertyName, $requiredConditionProperties, true)) {
                    throw new Exception(sprintf('Unknown property "%s" in conditions, only [%s] are accepted', $conditionPropertyName, implode(', ', $requiredConditionProperties)));
                }
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return $this
     */
    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }
}
