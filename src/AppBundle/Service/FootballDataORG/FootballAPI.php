<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 16.08.17
 * Time: 14:07
 */

namespace AppBundle\Service\FootballDataORG;

use AppBundle\Entity\League;
use AppBundle\Entity\Season;
use AppBundle\Entity\League as LeagueORM;
use AppBundle\Entity\Team;
use AppBundle\Entity\TeamLeague;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class FootballAPI extends Request
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $baseURI;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var array|\stdClass
     */
    private $data;

    /**
     * @var array
     */
    private $leagueMapping;

    /**
     * @var League
     */
    private $league;

    /**
     * League constructor.
     * @param array $config
     * @param ObjectManager $em
     * @throws Exception
     */
    public function __construct(array $config, $em)
    {
        if(array_key_exists('token', $config)){
            parent::__construct($config['token']);
        }else{
            throw new Exception("No token in config parameters!");
        }

        $this->em = $em;

        if(array_key_exists('language', $config)){
            $this->language = $config['language'];
        }

        if(array_key_exists('base_uri', $config)){
            $this->baseURI = $config['base_uri'];
        }

        if(array_key_exists('api_version', $config)){
            $this->apiVersion = $config['api_version'];
        }
    }

    /**
     * @param string $request
     * @return string
     */
    private function buildURI(string $request)
    {
        return $this->baseURI. '/'. $this->apiVersion. '/'. $request;
    }

    /**
     * @param string $season
     */
    public function fetchLeagueList(string $season)
    {
        $this->data = $this->request(
            'GET',
            $this->buildURI("competitions/?season=$season")
        );
    }

    public function fetchTeamList($competition)
    {
        $this->data = $this->request(
            'GET',
            $this->buildURI("competitions/$competition/teams")
        );

        $this->league = $this->em->getRepository('AppBundle:League')->findOneBy([
            'code' => $competition
        ]);
    }

    public function updateTeams()
    {
        if(!$this->league){
            throw new Exception("League undefined!");
        }
        foreach ($this->data->teams as $teamData){
            $urlParts = explode("/", $teamData->_links->self->href);
            $teamCode = (int)array_pop($urlParts);
            if($teamCode <= 0){
                throw new Exception("Team id not found ". $teamData->name);
            }
            $team = $this->em->getRepository('AppBundle:Team')->findOneBy([
                'code' => $teamCode
            ]);
            if(!$team){
                $team = new Team();
                $team->setName($teamData->name);
                $team->setCode($teamCode);
                $team->setCodeName($teamData->code);
                $team->setShortName($teamData->shortName);
                $team->setMarketValue($teamData->squadMarketValue);
                $team->setCrestURL($teamData->crestUrl);
                $this->em->persist($team);
            }

            $teamLeague = $this->em->getRepository('AppBundle:TeamLeague')->findOneBy([
                'team' => $team,
                'league' => $this->league
            ]);
            if(!$teamLeague){
                $teamLeague = new TeamLeague();
                $teamLeague->setLeague($this->league);
                $teamLeague->setTeam($team);
                $this->em->persist($teamLeague);
            }
        }
        $this->em->flush();
    }

    public function updateLeagues()
    {
        if(!is_array($this->data)){
            return null;
        }

        foreach ($this->data as $object){
            $season = $this->em->getRepository('AppBundle:Season')->findOneBy([
                'year' => $object->year
            ]);
            if(!$season){
                $season = new Season();
                $season->setYear($object->year);

                $this->em->persist($season);
                $this->em->flush();
            }

            $mapping = new \stdClass();
            $mapping->code = $object->id;

            $league = $this->em->getRepository('AppBundle:League')->findOneBy([
                'season' => $season,
                'code' => $object->id
            ]);
            $dateUpdate = new \DateTime($object->lastUpdated);
            $dateUpdate->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            if(!$league){
                $league = new LeagueORM();
                $league->setTranslatableLocale($this->language);
                $league->setCode($object->id);
                $league->setSeason($season);
                $league->setCaption($object->caption);
                $league->setName($object->league);
                $league->setCurrentMatchday($object->currentMatchday);
                $league->setNumberOfMatchdays($object->numberOfMatchdays);
                $league->setNumberOfTeams($object->numberOfTeams);
                $league->setNumberOfGames($object->numberOfGames);
                $league->setLasUpdate($dateUpdate);

                $this->em->persist($league);
            }else{
                if($league->getLasUpdate()->getTimestamp() != $dateUpdate->getTimestamp()){
                    $mapping->updated = true;
                }else{
                    $mapping->updated = false;
                }
            }

            $this->leagueMapping[] = $mapping;
        }

        $this->em->flush();
    }
}