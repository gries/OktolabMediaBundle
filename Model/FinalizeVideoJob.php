<?php
namespace Oktolab\MediaBundle\Model;

use Bprs\CommandLineBundle\Model\BprsContainerAwareJob;
use Oktolab\MediaBundle\Entity\Episode;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * checks if all media of an episode respond with 200 and sets the active flag accordingly
 */
class FinalizeVideoJob extends BprsContainerAwareJob
{
    private $em;
    private $media_service;
    private $asset_helper_service;
    private $logger;

    public function getName() {
        return 'Finalize Episode';
    }

    public function perform() {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->media_service = $this->getContainer()->get('oktolab_media');
        $this->logger = $this->getContainer()->get('logger');
        $episode = $this->media_service->getEpisode($this->args['uniqID']);
        $this->logger->info(sprintf('[FinalizeVideoJob] Started Finalize of Episode %s', $this->args['uniqID']));
        if ($episode) {
            $this->asset_helper_service = $this->getContainer()->get('bprs.asset_helper');

            $this->media_service->setEpisodeStatus($this->args['uniqID'], Episode::STATE_FINALIZING);

            if ($this->checkMediaStatus($episode)) {
                $episode->setIsActive(true);
                $this->em->persist($episode);
                $this->em->flush();
                $this->media_service->setEpisodeStatus($this->args['uniqID'], Episode::STATE_READY);
            } else {
                $episode->setIsActive(false);
                $this->em->persist($episode);
                $this->em->flush();
                $this->media_service->setEpisodeStatus($this->args['uniqID'], Episode::STATE_FINALIZING_FAILED);
            }

        } else { // episode not found
            $this->logger->error(sprintf('FINALIZE JOB could not find EPISODE with UniqID [%s]', $this->args['uniqID']));
        }
    }

    private function checkMediaStatus($episode)
    {
        $is_active = true;

        foreach ($episode->getMedia() as $media) {
            if ($media->getAsset()) {
                $url = $this->asset_helper_service->getAbsoluteUrl($media->getAsset());
                $this->logger->info(sprintf('[FinalizeVideoJob] Check Media Status of %s', $url));
                $client = new Client();
                $response = $client->request('GET', $url);
                if ($response->getStatusCode() != Response::HTTP_OK) {
                    $is_active = false;
                }
            } else {
                $is_active = false;
            }
        }

        return $is_active;
    }
}
