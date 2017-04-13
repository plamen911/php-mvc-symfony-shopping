<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Photo
 *
 * @ORM\Table(name="photos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhotoRepository")
 */
class Photo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/jpg", "image/jpeg", "image/png", "image/gif" }, maxSize="2M")
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Product", inversedBy="photos")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * Photo constructor.
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return Photo
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Photo
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Photo
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    public static function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . self::getUploadDir();
    }

    public static function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/';
    }

    public static function getUploadDirOriginals()
    {
        return self::getUploadDir() . 'originals/';
    }

    public static function getUploadDirThumbs()
    {
        return self::getUploadDir() . 'th/';
    }

    public static function getUploadDirLarge()
    {
        return self::getUploadDir() . 'lg/';
    }

    // http://atomiku.com/2012/11/php-function-for-resizing-and-cropping-images-with-gd/
    // Resizing then cropping to a 200px square: resize_image('source_image.jpg', 'image_cropped.jpg', 200, 200);
    // Resizing to width of 200px: resize_image('source_image.jpg', 'image_resized', 200, false);
    public static function resizeImage($file, $destination, $w, $h)
    {
        //Get the original image dimensions + type
        list($source_width, $source_height, $source_type) = getimagesize($file);

        //Figure out if we need to create a new JPG, PNG or GIF
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext == "jpg" || $ext == "jpeg") {
            $source_gdim = imagecreatefromjpeg($file);
        } elseif ($ext == "png") {
            $source_gdim = imagecreatefrompng($file);
        } elseif ($ext == "gif") {
            $source_gdim = imagecreatefromgif($file);
        } else {
            //Invalid file type? Return.
            return;
        }

        //If a width is supplied, but height is false, then we need to resize by width instead of cropping
        if ($w && !$h) {
            $ratio = $w / $source_width;
            $temp_width = $w;
            $temp_height = $source_height * $ratio;

            $desired_gdim = imagecreatetruecolor($temp_width, $temp_height);
            imagecopyresampled(
                $desired_gdim,
                $source_gdim,
                0, 0,
                0, 0,
                $temp_width, $temp_height,
                $source_width, $source_height
            );
        } else {
            $source_aspect_ratio = $source_width / $source_height;
            $desired_aspect_ratio = $w / $h;

            if ($source_aspect_ratio > $desired_aspect_ratio) {
                /*
                 * Triggered when source image is wider
                 */
                $temp_height = $h;
                $temp_width = ( int )($h * $source_aspect_ratio);
            } else {
                /*
                 * Triggered otherwise (i.e. source image is similar or taller)
                 */
                $temp_width = $w;
                $temp_height = ( int )($w / $source_aspect_ratio);
            }

            /*
             * Resize the image into a temporary GD image
             */

            $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
            imagecopyresampled(
                $temp_gdim,
                $source_gdim,
                0, 0,
                0, 0,
                $temp_width, $temp_height,
                $source_width, $source_height
            );

            /*
             * Copy cropped region from temporary image into the desired GD image
             */

            $x0 = ($temp_width - $w) / 2;
            $y0 = ($temp_height - $h) / 2;
            $desired_gdim = imagecreatetruecolor($w, $h);
            imagecopy(
                $desired_gdim,
                $temp_gdim,
                0, 0,
                $x0, $y0,
                $w, $h
            );
        }

        /*
         * Render the image
         * Alternatively, you can save the image in file-system or database
         */

        if ($ext == "jpg" || $ext == "jpeg") {
            ImageJpeg($desired_gdim, $destination, 100);
        } elseif ($ext == "png") {
            ImagePng($desired_gdim, $destination);
        } elseif ($ext == "gif") {
            ImageGif($desired_gdim, $destination);
        } else {
            return;
        }

        ImageDestroy($desired_gdim);
    }
}

