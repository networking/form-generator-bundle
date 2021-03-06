form-generator-bundle
=====================

###This bundle only works with the networking/init-cms-bundle!

This bundle will provide a form generator fo the init cms

Install bundle via composer.

    "require": {
        ....
        "networking/form-generator-bundle": "dev-master",
        ...
    }
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:networking/form-generator-bundle.git"
        }
    ],

Update your AppKernel.php with NetworkingFormGeneratorBundle, the FOSRestBundle and the LiuggioExcelBundle

```
<?php
	// app/AppKernel.php
	public function registerbundles()
	{
	    return array(
	        // ...
            new Networking\InitCmsBundle\NetworkingInitCmsBundle(),
            new Networking\FormGeneratorBundle\NetworkingFormGeneratorBundle,
            new FOS\RestBundle\FOSRestBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
	    );
	}
```


    
In your routing.yaml file, and the routing for the search action

    networking_form_generator:
        resource: "@NetworkingFormGeneratorBundle/Resources/config/routing.yaml"
        prefix:   /
    
Add the form page content to the init cms content type configuration

```
networking_init_cms:
    ...
    content_types:
        ...
        - { name: 'Formular' , class: 'Networking\FormGeneratorBundle\Entity\FormPageContent'}
 
```

And in your template which outputs the dynamic cms content add the following
code to render the form

```
{% if form_page_content is defined %}
    {{ render(controller('NetworkingFormGeneratorBundle:FrontendForm:renderForm', {'form': form_page_content})) }}
{% endif %}
``` 
    
Add your configuration for the sender email address
    
```
networking_form_generator:
    from_email: "sender@email.com"
```

        


