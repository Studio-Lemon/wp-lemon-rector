<?php

class TestClass
{
   public function test_method()
   {
      $this->add_class(['bg-pill']);
      $this->add_class(['section', 'has-background']);
      $this->add_class(['extra-class']);
   }
}
