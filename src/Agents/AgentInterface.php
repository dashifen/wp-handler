<?php

namespace Dashifen\WPHandler\Agents;

interface AgentInterface
{
  
  // at this time, the purpose of this interface is simply to identify an
  // Agent.  this is necessary because each type of Agent extends the Handler
  // that they work with, e.g. the AbstractPluginAgent extends the
  // AbstractPluginHandler.  as a result, there's no single class, other than
  // AbstractHandler, from which all Agents descend.  adding this interface
  // allows us to more easily answer the question:  is this object an Agent?
  
}
