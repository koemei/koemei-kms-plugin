<?php $unitTestSchema ='
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <xsd:element name="metadata">
    <xsd:complexType>
      <xsd:sequence>
        <xsd:element id="md_E78E45B9-4319-7829-19D5-C51C74193E79" name="Topics" minOccurs="0" maxOccurs="unbounded">
          <xsd:annotation>
            <xsd:documentation></xsd:documentation>
            <xsd:appinfo>
              <label>Topics</label>
              <key>Topics</key>
              <searchable>true</searchable>
              <timeControl>false</timeControl>
              <description></description>
            </xsd:appinfo>
          </xsd:annotation>
          <xsd:simpleType>
            <xsd:restriction base="listType">
              <xsd:enumeration value="topic one"/>
              <xsd:enumeration value="topic two"/>
              <xsd:enumeration value="topic three"/>
              <xsd:enumeration value="last topic"/>
            </xsd:restriction>
          </xsd:simpleType>
        </xsd:element>
      </xsd:sequence>
    </xsd:complexType>
  </xsd:element>
  <xsd:complexType name="textType">
    <xsd:simpleContent>
      <xsd:extension base="xsd:string"/>
    </xsd:simpleContent>
  </xsd:complexType>
  <xsd:complexType name="dateType">
    <xsd:simpleContent>
      <xsd:extension base="xsd:long"/>
    </xsd:simpleContent>
  </xsd:complexType>
  <xsd:complexType name="objectType">
    <xsd:simpleContent>
      <xsd:extension base="xsd:string"/>
    </xsd:simpleContent>
  </xsd:complexType>
  <xsd:simpleType name="listType">
    <xsd:restriction base="xsd:string"/>
  </xsd:simpleType>
</xsd:schema>';
?>
