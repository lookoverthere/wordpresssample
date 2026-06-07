## Install

In theory, we would only need to commit plugins/partner-organizations for this to work. However, for this sample, I have included the entire wordpress installation.

Also for a "real world" usage of this product, what I would do is export my local wordpress database into a .sql script that can be shared so that all the custom plugins and whatever theme setup is there.

For this project, all that needs to be done is:

1. `git clone <url>`
2. Run `docker-compose up -d`
3. Install wordpress and enable the plugin partner-organizations in the admin Plugins page.

Also be sure the permalink is set to Post Name so the API can be used. 

## Overview of architectural and technical approaches

I like to structure the plugins so that it is placed neatly into a folder structure to modify things easily. I keep everything into a folder for assets/, includes/ and templates/. The main entry point of the plugin just serves as a data and init() functions that get called.

I have structured it into each of the key points of this task:

classes/partner.php - This is important to load before anything else because it registers the custom post types as well as the taxonomy that sets up the categories (nonprofit, corporate, etc). This is important to load first because it will register the proper data for the rest of the plugin to use the custom post type.This file is mostly just boilerplating the custom post type mostly to show on the side bar as well as a list of custom post types that were created. Some important things to consider are the columns that get displayed and in this case I just showed everything since it is small like logo, url and title. For larger projects, it’s important to show the vital information so that one can quickly glance at it and be able to modify it. 

classes/meta.php - This is the file that handles the creation of the custom post type. The big thing is that it loads a custom template in the template/ folder to render a page that lets admins be able to upload and on top of that load the javascript to handle the upload/removal of media items. The heavy hitter part of this file is the save() function in my opinion because it is first verifying the nonce of the image upload and on top of that saving the meta data for the post that includes the custom fields that it requires. The important thing is to make sure the template ultimately shows all the proper data.

classes/block.php - This is the file that initializes the block for the widget editor for posts. You will find the widget under WIDGETS of the post and by default it will show ALL categories. You can fine tune it to category, name, and count limit. An important file is the javascript file in assets that gets loaded by the register_block_type function. This file will pass back the render data with the attributes between the front end to the back end process. The important thing is registering the proper component blocks and making sure the names match that in the block.php file so that when you run a query search you can filter the query. The html is very basic but going back to the initial plugin loader at partner-organizations.php you will see that it also loads a .css style that ultimately when you view it on the front end page post, it will show the proper styling.

classes/api.php - This will register the endpoint for the API call. You can pass in arguments such as search, category, title, etc. Personally I like to keep the API so that there is a default pagination so that not everything gets dumped into a huge file. Ideally whatever is called it will set up proper pagination so that it gets per page and limit. I have set the limit to a really low number so that it can be illustrated quickly. Also as a side note, I like to use Postman to test API endpoints so that it can be called properly. In a situation with authentication, it makes it a breeze to use.

## Key tradeoffs and decisions

The big thing I didn’t liked about the block code I made was that I hardcoded the categories. Sure having a default “All” is good but I would want to pull in all the categories that I can.

As for the custom post type itself, I felt that it was pretty straight forward in terms of creation but I think the big thing is validation as well as implementing better data handling and checking and limiting those aspects. 

## What would you improve or expand on

I think the biggest thing is that none of my classes use the API endpoint that I created. If I were go back, I think I would use more of it as an api call to pull back the json data. Also caching is a huge thing as each time the page is called, it is doing a db fetch in the backend which will impact a high use site.

I think having a specific API key that could be added to the header would be helpful in this situation where only the site itself would be able to call it. Also checking the origin header to make sure that nothing outside of it is being called would be important as well.

## AI Usage

I used Cursor as my main AI tool with Claude. I used the plan mode to determine some of the set up and usage to determine what sort of best way to make the plugin.I have used it to run some codes, especially to set up the register_* functions to verify that I have all the proper arguments. I have also used it to set up the docker-compose yaml file but have modified it to fit some things such as the database users, names, and making sure the correct package name is used.
