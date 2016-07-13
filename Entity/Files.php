<?php
/**
 * Created by PhpStorm.
 * User: dhaouadi_a
 * Date: 04/05/2016
 * Time: 14:10
 */

namespace ProgressAssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Files
 *
 *
 * @ORM\Table(name="upload_files")
 * @ORM\Entity(repositoryClass="ProgressAssetBundle\Repository\FilesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Files
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     * @Assert\File(maxSize="6000000" )
     */
    public $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_upload", type="datetime", nullable=true)
     */
    private $dateUpload;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileSize;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * */
    public function setName($name)
    {
        $this->name = $name;

    }


    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }


    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }


    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;

    }

    /**
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->fileSize;

    }

    /**
     * @param mixed $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $this->formatBytes($fileSize);
    }

    /**
     * fileSize en Mo
     *
     * @param $fileSize
     * @return string
     */
    public function formatBytes($fileSize)
    {
        $base = log($fileSize, 1024);
        $suffixes = array('', 'Ko', 'Mo', 'Go', 'To');

        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }


    /**
     * @ORM\PrePersist
     */
    public function init()
    {
        $this->dateUpload = new \DateTime();

    }

    /**
     * Set dateUpload
     *
     * @param $dateUpload
     * @return $this
     */
    public function setDateUpload($dateUpload)
    {
        $this->dateUpload = $dateUpload;

        return $this;
    }

    /**
     * get dateUpload
     *
     * @return \DateTime
     */
    public function getDateUpload()
    {
        return $this->dateUpload;
    }

    /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    /**
     * @return null|string
     */
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * @return string
     */
    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/documents';
    }


    /**
     *
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move($this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

}