<?php
/**
 * This file is part of the Tmdb PHP API created by Michael Roterman.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Tmdb
 * @author Michael Roterman <michael@wtfz.net>
 * @copyright (c) 2013, Michael Roterman
 * @version 0.0.1
 */
namespace Tmdb\Factory;

use Tmdb\Factory\Common\ChangeFactory;
use Tmdb\Factory\Common\VideoFactory;
use Tmdb\Factory\People\CastFactory;
use Tmdb\Factory\People\CrewFactory;
use Tmdb\HttpClient\HttpClient;
use Tmdb\Model\Common\GenericCollection;
use Tmdb\Model\Person\CastMember;
use Tmdb\Model\Person\CrewMember;
use Tmdb\Model\Common\ExternalIds;
use Tmdb\Model\Tv\Episode;

/**
 * Class TvEpisodeFactory
 * @package Tmdb\Factory
 */
class TvEpisodeFactory extends AbstractFactory
{
    /**
     * @var People\CastFactory
     */
    private $castFactory;

    /**
     * @var People\CrewFactory
     */
    private $crewFactory;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var Common\VideoFactory
     */
    private $videoFactory;

    /**
     * @var Common\ChangeFactory
     */
    private $changesFactory;

    /**
     * Constructor
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->castFactory    = new CastFactory($httpClient);
        $this->crewFactory    = new CrewFactory($httpClient);
        $this->imageFactory   = new ImageFactory($httpClient);
        $this->videoFactory   = new VideoFactory($httpClient);
        $this->changesFactory = new ChangeFactory($httpClient);

        parent::__construct($httpClient);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data = [])
    {
        if (!$data) {
            return null;
        }

        $tvEpisode = new Episode();

        if (array_key_exists('credits', $data)) {
            if (array_key_exists('cast', $data['credits'])) {
                $tvEpisode
                    ->getCredits()
                    ->setCast(
                        $this->getCastFactory()
                            ->createCollection(
                                $data['credits']['cast'],
                                new CastMember()
                            )
                    );
            }

            if (array_key_exists('crew', $data['credits'])) {
                $tvEpisode->getCredits()->setCrew(
                    $this->getCrewFactory()->createCollection(
                        $data['credits']['crew'],
                        new CrewMember()
                    )
                );
            }
        }

        /** External ids */
        if (array_key_exists('external_ids', $data)) {
            $tvEpisode->setExternalIds(
                $this->hydrate(new ExternalIds(), $data['external_ids'])
            );
        }

        /** Images */
        if (array_key_exists('images', $data)) {
            $tvEpisode->setImages($this->getImageFactory()->createCollectionFromTvEpisode($data['images']));
        }

        if (array_key_exists('still_path', $data)) {
            $tvEpisode->setStillImage($this->getImageFactory()->createFromPath($data['still_path'], 'still_path'));
        }

        if (array_key_exists('videos', $data)) {
            $tvEpisode->setVideos($this->getVideoFactory()->createResultCollection($data['videos']));
        }

        if (array_key_exists('changes', $data)) {
            $tvEpisode->setChanges($this->getChangesFactory()->createCollection($data['changes']));
        }

        return $this->hydrate($tvEpisode, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function createCollection(array $data = [])
    {
        $collection = new GenericCollection();

        foreach ($data as $item) {
            $collection->add(null, $this->create($item));
        }

        return $collection;
    }

    /**
     * @param  \Tmdb\Factory\People\CastFactory $castFactory
     * @return $this
     */
    public function setCastFactory($castFactory)
    {
        $this->castFactory = $castFactory;

        return $this;
    }

    /**
     * @return \Tmdb\Factory\People\CastFactory
     */
    public function getCastFactory()
    {
        return $this->castFactory;
    }

    /**
     * @param  \Tmdb\Factory\People\CrewFactory $crewFactory
     * @return $this
     */
    public function setCrewFactory($crewFactory)
    {
        $this->crewFactory = $crewFactory;

        return $this;
    }

    /**
     * @return \Tmdb\Factory\People\CrewFactory
     */
    public function getCrewFactory()
    {
        return $this->crewFactory;
    }

    /**
     * @param  \Tmdb\Factory\ImageFactory $imageFactory
     * @return $this
     */
    public function setImageFactory($imageFactory)
    {
        $this->imageFactory = $imageFactory;

        return $this;
    }

    /**
     * @return \Tmdb\Factory\ImageFactory
     */
    public function getImageFactory()
    {
        return $this->imageFactory;
    }

    /**
     * @param  \Tmdb\Factory\Common\VideoFactory $videoFactory
     * @return $this
     */
    public function setVideoFactory($videoFactory)
    {
        $this->videoFactory = $videoFactory;

        return $this;
    }

    /**
     * @return \Tmdb\Factory\Common\VideoFactory
     */
    public function getVideoFactory()
    {
        return $this->videoFactory;
    }

    /**
     * @param  \Tmdb\Factory\Common\ChangeFactory $changesFactory
     * @return $this
     */
    public function setChangesFactory($changesFactory)
    {
        $this->changesFactory = $changesFactory;

        return $this;
    }

    /**
     * @return \Tmdb\Factory\Common\ChangeFactory
     */
    public function getChangesFactory()
    {
        return $this->changesFactory;
    }
}
