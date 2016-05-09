<?php


namespace Render;

use Render\IconHelper\SimpleIconHelper;
use Render\ImageToolFactory\LiveImageToolFactory;
use Render\InfoStorage\MediaInfoStorage\ArrayBasedMediaInfoStorage;
use Render\InfoStorage\MediaInfoStorage\LiveArrayMediaInfoStorage;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use Render\MediaUrlHelper\IMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\LiveValidationHelper;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;

/**
 * Class LiveMediaContext
 *
 * MediaContext for Live Pages.
 * Heavily relies on constants and files generated by Creator.
 * TODO: move this file out of \Render ?!
 */
class LiveMediaContext extends MediaContext
{
  /**
   * @var ValidationHelperInterface
   */
  private $mediaValidationHelper;

  /**
   * @var IMediaUrlHelper
   */
  private $mediaUrlHelper;

  /**
   * @param bool $writeSecureFile
   */
  public function __construct($writeSecureFile = false)
  {
    $this->mediaValidationHelper = $this->createMediaValidationHelper($writeSecureFile);
    $this->mediaUrlHelper = $this->createMediaUrlHelper($this->mediaValidationHelper);
    $mediaInfoStorage = $this->createMediaInfoStorage($this->mediaUrlHelper);
    $imageToolFactory = $this->createImageToolFactory();
    parent::__construct($mediaInfoStorage, $imageToolFactory);
  }

  /**
   * @return ValidationHelperInterface
   */
  public function getMediaValidationHelper()
  {
    return $this->mediaValidationHelper;
  }

  /**
   * @return IMediaUrlHelper
   */
  public function getMediaUrlHelper()
  {
    return $this->mediaUrlHelper;
  }

  /**
   * @param $writeSecureFile
   *
   * @return ValidationHelperInterface
   */
  protected function createMediaValidationHelper($writeSecureFile)
  {
    $mediaCache = new MediaCache(MEDIA_CACHE_PATH);
    $mediaModFilePath = DATA_PATH . DIRECTORY_SEPARATOR . 'media.mod.php';
    return new LiveValidationHelper($mediaModFilePath, $mediaCache, (bool)$writeSecureFile);
  }

  /**
   * @param ValidationHelperInterface $mediaValidationHelper
   *
   * @return IMediaUrlHelper
   */
  protected function createMediaUrlHelper(ValidationHelperInterface $mediaValidationHelper)
  {
    return new CDNMediaUrlHelper(
        $mediaValidationHelper,
        MEDIA_WEBPATH . '/cdn.php'
    );
  }

  /**
   * @param IMediaUrlHelper $mediaUrlHelper
   *
   * @return ArrayBasedMediaInfoStorage
   */
  protected function createMediaInfoStorage(IMediaUrlHelper $mediaUrlHelper)
  {
    /** @noinspection PhpIncludeInspection */
    $albumList = include(DATA_PATH . DIRECTORY_SEPARATOR . 'album.php');
    /** @noinspection PhpIncludeInspection */
    $mediaItemMap = include(DATA_PATH . DIRECTORY_SEPARATOR . 'media.php');
    $iconHelper = new SimpleIconHelper(ICON_FILES_PATH, 'icon_fallback.png');
    return new LiveArrayMediaInfoStorage(MEDIA_FILES_PATH, $mediaItemMap, $mediaUrlHelper, $iconHelper, $albumList);
  }

  /**
   * @return LiveImageToolFactory
   */
  protected function createImageToolFactory()
  {
    return new LiveImageToolFactory(LIBRARY_PATH);
  }
}