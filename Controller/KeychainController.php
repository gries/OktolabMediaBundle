<?php

namespace Oktolab\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oktolab\MediaBundle\Model\MediaService;

use Bprs\AppLinkBundle\Entity\Keychain;
/**
 * Keychain controller. Allows backend importing onsite.
 *
 * @Route("/oktolab_media")
 */
class KeychainController extends Controller
{
    /**
     * @Route("/list_keychains", name="oktolab_media_list_keychains")
     * @Template()
     */
    public function listKeychainsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $keys = $em->getRepository('BprsAppLinkBundle:Key')->findBy(array('role' => MediaService::ROLE_READ));

        return array('keys' => $keys);
    }

    /**
     * @Route("/show_keychain/{keychain}", name="oktolab_media_show_keychain")
     * @Template()
     */
    public function showKeychainAction(Keychain $keychain)
    {
        return array('keychain' => $keychain);
    }

    /**
     * @Route("/import/episode", name="oktolab_media_local_import_episode")
     * @Method("POST")
     */
    public function importEpisodeAction(Request $request)
    {
        $uniqID = $request->request->get('uniqID');
        if ($uniqID) {
            $user = $request->request->get('user');
            $em = $this->getDoctrine()->getManager();
            $apiuser = $em->getRepository('BprsAppLinkBundle:Keychain')->findOneBy(array('user'=> $user));

            $this->get('oktolab_media')->addEpisodeJob($apiuser, $uniqID);
            return new Response("", Response::HTTP_ACCEPTED);
        }
        return new Response("", Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/import/series", name="oktolab_media_local_import_series")
     * @Method("POST")
     */
    public function importSeriesAction(Request $request)
    {
        $uniqID = $request->request->get('uniqID');
        if ($uniqID) {
            $user = $request->request->get('user');
            $em = $this->getDoctrine()->getManager();
            $apiuser = $em->getRepository('BprsAppLinkBundle:Keychain')->findOneBy(array('user'=> $user));

            $this->get('oktolab_media')->addSeriesJob($apiuser, $uniqID);
            return new Response("", Response::HTTP_ACCEPTED);
        }
        return new Response("", Response::HTTP_BAD_REQUEST);
    }
} ?>