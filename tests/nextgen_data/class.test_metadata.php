<?php

require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

class C_Test_Nextgen_Metadata extends C_Test_Component_Base
{

    function __construct($label='C_NextGen_Metadata Test')
    {
        parent::__construct($label);
    }

    /**
     * Create a gallery and image for testing purposes
     */
    function setUp()
    {
        parent::setUp();
        // Get the mappers required for these tests
        $this->gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
        $this->image_mapper   = $this->get_registry()->get_utility('I_Image_Mapper');

        // Create test gallery to work with
        $this->gallery = (object) array('title' => 'NextGen Gallery');
        $this->gid = $this->gallery_mapper->save($this->gallery);

        // Create image to work with
        $this->image = (object)array(
            'alttext'   => 'test-image',
            'filename'  => 'test.jpg',
            'galleryid' => $this->gid
        );
        $this->pid = $this->image_mapper->save($this->image);

        $this->galleries_to_cleanup = array($this->gallery);
        $this->images_to_cleanup    = array($this->image);
    }


    function tearDown()
    {
        parent::tearDown();

        // Delete any temporary galleries and images we might have created
        $this->gallery_mapper->destroy($this->gid);
        $this->image_mapper->destroy($this->pid);

        foreach ($this->galleries_to_cleanup as $gid) {
            $this->gallery_mapper->destroy($gid);
        }

        foreach ($this->images_to_cleanup as $pid) {
            $this->image_mapper->destroy($pid);
        }
    }

    function test_something()
    {
        $CNGM = new C_NextGen_Metadata($this->image);
        $meta_data = $CNGM->get_common_meta();

        $this->assertEqual(
            'Hands',
            $meta_data['caption'],
            'get_common_meta() returned incorrect EXIF data'
        );

        $this->assertEqual(
            3888,
            $meta_data['width'],
            'get_common_meta() returned incorrect image width'
        );

        $this->assertEqual(
            2592,
            $meta_data['height'],
            'get_common_meta() returned incorrect image height'
        );
    }
}
